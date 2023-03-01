<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) Al Poole <netstar@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Service\AppLogger;
use App\Utils\ApiKey;
use Doctrine\Persistence\ManagerRegistry;
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
            throw new \Exception('Missing parameters');
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

        $logger->info('API key regeneated', ['user_id' => $user->getId()]);

        return new JsonResponse(['api-key' => $newKey, 'message' => 'ok']);
    }
}
