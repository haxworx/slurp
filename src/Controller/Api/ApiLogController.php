<?php

// src/Controller/Api/LogController.php

namespace App\Controller\Api;

use App\Entity\RobotSettings;
use App\Entity\RobotLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Persistence\ManagerRegistry;

class ApiLogController extends AbstractController
{
    #[Route('/api/log', name: 'app_api_log', format: 'json')]
    public function index(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $content = json_decode($request->getContent(), false);
        if ((!$content) || (!isset($content->lastId)) || (!isset($content->launchId)) || (!isset($content->botId)) || (!isset($content->token))) {
            throw new \InvalidArgumentException('Missing parameters');
        }

        $lastId = $content->lastId;
        $launchId = $content->launchId;
        $botId = $content->botId;
        $token = $content->token;

        if (!$doctrine->getRepository(RobotSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new AccessDeniedException('Bot not owned by user.');
        }

        if (!$this->isCsrfTokenValid('log', $token)) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }

        $logs = $doctrine->getRepository(RobotLog::class)->findAllNew($launchId, $lastId);
        $n = count($logs);
        if ($n) {
            $txt = "";
            $content->lastId = $logs[$n - 1]->getId();
            foreach ($logs as $log) {
                $msg = $log->getMessage();
                $txt .= $msg['message'] . "\n";
            }
            $content->logs = $txt;
        }
    
        $response = new JsonResponse();
        $response->setContent(json_encode($content));

        return $response;
    }
}
