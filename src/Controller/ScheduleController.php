<?php

// src/Controller/ScheduleController.php

namespace App\Controller;

use App\Entity\RobotSettings;
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

        $settings = new RobotSettings();

        $form = $this->createForm(RobotSettingsType::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = $doctrine->getRepository(RobotSettings::class);

            $entityManager = $doctrine->getManager();
            $settings->setUserId($this->getUser()->getId());

            $entityManager->persist($settings);
            $entityManager->flush();

            $notifier->send(new Notification('Robot scheduled.', ['browser']));

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('schedule/index.html.twig', [
            'form' => $form,
        ]);
    }
}
