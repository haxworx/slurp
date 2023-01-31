<?php

namespace App\Controller;

use App\Entity\RobotSettings;
use App\Entity\RobotData;
use App\Entity\RobotLaunches;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app_stats')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $stats = [];

        $settings = $doctrine->getRepository(RobotSettings::class)->findAllByUserId($user->getId());
        foreach ($settings as $setting) {
            $botId = $setting->getId();
            $launch = $doctrine->getRepository(RobotLaunches::class)->findLastLaunchByBotId($botId);
            $launchCount = $doctrine->getRepository(RobotLaunches::class)->getCountByBotId($botId);
            $recordsCount = $doctrine->getRepository(RobotData::class)->getCountByBotId($botId);
            $stat = [
                'bot_id' => $botId,
                'name' => sprintf("(%s) %s", $setting->getScheme(), $setting->getDomainName()),
                'last_started' => $launch?->getStartTime(),
                'last_finished' => $launch?->getEndTime() ?? "n/a",
                'total_records' => $recordsCount,
                'total_launches' => $launchCount,
            ];
            $stats[] = $stat;
        }

        return $this->render('stats/index.html.twig', [
            'stats' => $stats,
        ]);
    }
}
