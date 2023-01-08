<?php

// src/Controller/ScheduleController.php

namespace App\Controller;

use App\Entity\RobotSettings;
use App\Entity\GlobalSettings;
use App\Form\RobotSettingsType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

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
                $notifier->send(new Notification('Reached maximum number of robots (' . $robotCount . ')', ['browser']));
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
            throw $this->createNotFoundException(
                'No robot for id: ' . $botId
            );
        }

        $form = $this->createForm(RobotSettingsType::class, $settings, [
            'save_button_label' => 'Update',
            'delete_button_hidden' => false,
            'import_sitemaps' => $settings->ImportSitemaps(),
            'address_readonly' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->IsValid()) {
            $repository = $doctrine->getRepository(RobotSettings::class);
            $exists = $repository->domainExists($settings, $user->getId());
            if ($exists) {
                $notifier->send(new Notification('Robot exists with that scheme and domain.', ['browser']));
            } else {
                $entityManager = $doctrine->getManager();
                $entityManager->persist($settings);
                $entityManager->flush();
                $notifier->send(new Notification('Robot updated', ['browser']));
            }
        }

        return $this->render('schedule/index.html.twig', [
            'form' => $form
        ]);
    }
}
