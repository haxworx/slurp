<?php

// src/Controller/LogController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
{
    #[Route('/log', name: 'app_log')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('log/index.html.twig');
    }
}
