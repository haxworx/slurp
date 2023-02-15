<?php

// src/Controller/ScheduleController.php

namespace App\Controller;

use App\Entity\GlobalSettings;
use App\Entity\RobotData;
use App\Entity\RobotLaunches;
use App\Entity\RobotLog;
use App\Entity\RobotSettings;
use App\Form\RobotSettingsType;
use App\Service\AppLogger;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;

class ScheduleController extends AbstractController
{
    public function __construct(AppLogger $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/schedule', name: 'app_schedule')]
    public function index(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $bot = new RobotSettings();

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->findOneBy(['id' => 1]);

        $form = $this->createForm(RobotSettingsType::class, $bot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = $doctrine->getRepository(RobotSettings::class);
            $robotCount = $repository->countByUserId($user->getId());
            if ($robotCount >= $globalSettings->getMaxRobots()) {
                $notifier->send(new Notification('Reached maximum number of robots ('.$robotCount.')', ['browser']));
            } else {
                $exists = $repository->domainExists($bot, $user->getId());
                if ($exists) {
                    $notifier->send(new Notification('Robot exists with that scheme and domain.', ['browser']));
                } else {
                    $entityManager = $doctrine->getManager();
                    $bot->setUserId($this->getUser()->getId());

                    $entityManager->persist($bot);
                    $entityManager->flush();

                    $notifier->send(new Notification('Robot scheduled.', ['browser']));

                    $this->logger->info(sprintf("robot %d scheduled (%s) for user %d", $bot->getId(), $bot->getName(), $user->getId()));

                    return $this->redirectToRoute('app_dashboard');
                }
            }
        }

        return $this->render('schedule/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/schedule/edit/{botId}', name: 'app_schedule_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier, int $botId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $bot = $doctrine->getRepository(RobotSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$bot) {
            throw $this->createNotFoundException('No robot for id: '.$botId);
        }

        $form = $this->createForm(RobotSettingsType::class, $bot, [
            'save_button_label' => 'Update',
            'import_sitemaps' => $bot->ImportSitemaps(),
            'domain_readonly' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->IsValid()) {
            $repository = $doctrine->getRepository(RobotSettings::class);
            $exists = $repository->domainExists($bot, $user->getId());
            $same = $repository->isSameEntity($bot, $user->getId());
            if ($exists && !$same) {
                $notifier->send(new Notification('Robot exists with that scheme and domain.', ['browser']));
            } else {
                $entityManager = $doctrine->getManager();
                $entityManager->persist($bot);
                $entityManager->flush();
                $notifier->send(new Notification('Robot updated', ['browser']));

                $this->logger->info(sprintf("robot %d edited (%s) for user %d", $bot->getId(), $bot->getName(), $user->getId()));
            }
        }

        return $this->render('schedule/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/schedule/delete/{botId}', name: 'app_schedule_delete', format: 'json', methods: ['POST'])]
    public function rm(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier, int $botId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $jsonContent = json_decode($request->getContent(), false);
        $botId = $jsonContent->botId;
        $token = $jsonContent->token;

        if (!$this->isCsrfTokenValid('robot-delete', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $bot = $doctrine->getRepository(RobotSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$bot) {
            throw $this->createNotFoundException('No robot for id: '.$botId);
        }

        $this->logger->info(sprintf("robot %d deleted (%s) for user %d", $bot->getId(), $bot->getName(), $user->getId()));

        // Remove database data.

        $doctrine->getRepository(RobotData::class)->deleteAllByBotId($botId);
        $doctrine->getRepository(RobotLog::class)->deleteAllByBotId($botId);
        $doctrine->getRepository(RobotLaunches::class)->deleteAllByBotId($botId);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($bot);
        $entityManager->flush();

        $notifier->send(new Notification('Robot removed.', ['browser']));



        return new JsonResponse(['message' => 'ok']);
    }
}
