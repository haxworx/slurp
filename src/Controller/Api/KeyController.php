<?php

// src/Controller/Api/KeyController.php

namespace App\Controller\Api;

use App\Utils\ApiKey;
use App\Service\AppLogger;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class KeyController extends AbstractController
{
    #[Route('/api/key/regenerate', name: 'app_api_key', methods: ['POST'], format: 'json')]
    public function index(Request $request, ManagerRegistry $doctrine, AppLogger $logger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $content = json_decode($request->getContent(), false);
        if ((!$content) || (!isset($content->token))) {
            throw new Exception('Missing parameters');
        }

        $token = $content->token;

        if (!$this->isCsrfTokenValid('regenerate-api-key', $token)) {
            throw $this->createAccessDeniedException('CSRF token invalid');
        }

        $newKey = ApiKey::generate();
        $user->setApiKey($newKey);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $logger->info(sprintf("user: %d regenerated API key", $user->getId()));

        return new JsonResponse(['api-key' => $newKey, 'message' => 'ok']);
    }
}
