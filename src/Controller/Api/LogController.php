<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) ai poole <imabiggeek@slurp.ai>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Entity\RobotLog;
use App\Entity\RobotSettings;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
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
            throw $this->createAccessDeniedException('Bot not owned by user.');
        }

        if (!$this->isCsrfTokenValid('log', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $logs = $doctrine->getRepository(RobotLog::class)->findAllNew($launchId, $lastId);
        $n = count($logs);
        if ($n) {
            $txt = '';
            $content->lastId = $logs[$n - 1]->getId();
            foreach ($logs as $log) {
                $msg = $log->getMessage();
                $dateTxt = $log->getTimeStamp()->format('Y-m-d H:i:s');
                $txt .= sprintf("%s:%s\n", $dateTxt, $msg['message']);
            }
            $content->logs = $txt;
        }

        $response = new JsonResponse();
        $response->setContent(json_encode($content));

        return $response;
    }
}
