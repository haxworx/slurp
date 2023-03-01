<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) Al Poole <netstar@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    private AppLogger $logger;

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
        if (!$globalSettings) {
            throw new \LogicException('No global settings found.');
        }

        $form = $this->createForm(RobotSettingsType::class, $bot);
        $form->handleRequest($request);

        $repository = $doctrine->getRepository(RobotSettings::class);
        $robotCount = $repository->countByUserId($user->getId());
        $robotCountMax = $globalSettings->getMaxRobots();

        if ($form->isSubmitted() && $form->isValid()) {
            $robotCount = $repository->countByUserId($user->getId());
            if ($robotCount >= $robotCountMax) {
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

                    $this->logger->info('robot scheduled', ['id' => $bot->getId(), 'name' => $bot->getName(), 'user_id' => $user->getId()]);

                    return $this->redirectToRoute('app_dashboard');
                }
            }
        }

        return $this->render('schedule/index.html.twig', [
            'form' => $form,
            'robot_count' => $robotCount,
            'robot_count_max' => $robotCountMax,
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

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->findOneBy(['id' => 1]);
        if (!$globalSettings) {
            throw new \LogicException('No global settings found.');
        }

        $form = $this->createForm(RobotSettingsType::class, $bot, [
            'save_button_label' => 'Update',
            'import_sitemaps' => $bot->ImportSitemaps(),
            'domain_readonly' => true,
        ]);

        $form->handleRequest($request);

        $repository = $doctrine->getRepository(RobotSettings::class);
        $robotCount = $repository->countByUserId($user->getId());
        $robotCountMax = $globalSettings->getMaxRobots();

        if ($form->isSubmitted() && $form->IsValid()) {
            $exists = $repository->domainExists($bot, $user->getId());
            $same = $repository->isSameEntity($bot, $user->getId());
            if ($exists && !$same) {
                $notifier->send(new Notification('Robot exists with that scheme and domain.', ['browser']));
            } else {
                $entityManager = $doctrine->getManager();
                $entityManager->persist($bot);
                $entityManager->flush();
                $notifier->send(new Notification('Robot updated', ['browser']));

                $this->logger->info('robot edited', ['id' => $bot->getId(), 'name' => $bot->getName(), 'user_id' => $user->getId()]);
            }
        }

        return $this->render('schedule/index.html.twig', [
            'form' => $form,
            'robot_count' => $robotCount,
            'robot_count_max' => $robotCountMax,
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

        $this->logger->info('robot deleted', ['id' => $bot->getId(), 'name' => $bot->getName(), 'user_id' => $user->getId()]);

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
