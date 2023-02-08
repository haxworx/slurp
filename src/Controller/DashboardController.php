<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use App\Entity\RobotSettings;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $instances = $doctrine->getRepository(RobotSettings::class)->findAllByUserId($user->getId());

        return $this->render('dashboard/index.html.twig', [
            'instances' => $instances,
        ]);
    }
}
