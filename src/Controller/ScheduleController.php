<?php

// src/Controller/ScheduleController.php

namespace App\Controller;

use App\Entity\GlobalSettings;
use App\Entity\RobotData;
use App\Entity\RobotLaunches;
use App\Entity\RobotLog;
use App\Entity\RobotSettings;
use App\Form\RobotSettingsType;
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
    #[Route('/schedule', name: 'app_schedule')]
    public function index(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $settings = new RobotSettings();

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->findOneBy(['id' => 1]);

        $form = $this->createForm(RobotSettingsType::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = $doctrine->getRepository(RobotSettings::class);
            $robotCount = $repository->countByUserId($user->getId());
            if ($robotCount >= $globalSettings->getMaxRobots()) {
                $notifier->send(new Notification('Reached maximum number of robots ('.$robotCount.')', ['browser']));
            } else {
                $exists = $repository->domainExists($settings, $user->getId());
                if ($exists) {
                    $notifier->send(new Notification('Robot exists with that scheme and domain.', ['browser']));
                } else {
                    $entityManager = $doctrine->getManager();
                    $settings->setUserId($this->getUser()->getId());

                    $entityManager->persist($settings);
                    $entityManager->flush();

                    $notifier->send(new Notification('Robot scheduled.', ['browser']));

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

        $settings = $doctrine->getRepository(RobotSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$settings) {
            throw $this->createNotFoundException('No robot for id: '.$botId);
        }

        $form = $this->createForm(RobotSettingsType::class, $settings, [
            'save_button_label' => 'Update',
            'import_sitemaps' => $settings->ImportSitemaps(),
            'domain_readonly' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->IsValid()) {
            $repository = $doctrine->getRepository(RobotSettings::class);
            $exists = $repository->domainExists($settings, $user->getId());
            $same = $repository->isSameEntity($settings, $user->getId());
            if ($exists && !$same) {
                $notifier->send(new Notification('Robot exists with that scheme and domain.', ['browser']));
            } else {
                $entityManager = $doctrine->getManager();
                $entityManager->persist($settings);
                $entityManager->flush();
                $notifier->send(new Notification('Robot updated', ['browser']));
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

        $settings = $doctrine->getRepository(RobotSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$settings) {
            throw $this->createNotFoundException('No robot for id: '.$botId);
        }

        // Remove database data.

        $doctrine->getRepository(RobotData::class)->deleteAllByBotId($botId);
        $doctrine->getRepository(RobotLog::class)->deleteAllByBotId($botId);
        $doctrine->getRepository(RobotLaunches::class)->deleteAllByBotId($botId);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($settings);
        $entityManager->flush();

        $notifier->send(new Notification('Robot removed.', ['browser']));

        return new JsonResponse(['message' => 'ok']);
    }
}
