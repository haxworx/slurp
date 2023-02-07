<?php

namespace App\Controller;

use App\Entity\RobotData;
use App\Entity\RobotLaunches;
use App\Entity\RobotSettings;
use App\Utils\Dates;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatsController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->launchesRepository = $doctrine->getRepository(RobotLaunches::class);
        $this->dataRepository = $doctrine->getRepository(RobotData::class);
    }

    #[Route('/stats', name: 'app_stats')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $stats = [];

        $settings = $doctrine->getRepository(RobotSettings::class)->findAllByUserId($user->getId());
        foreach ($settings as $setting) {
            $botId = $setting->getId();
            $launch = $this->launchesRepository->findLastLaunchByBotId($botId);
            $launchCount = $this->launchesRepository->getCountByBotId($botId);
            $recordsCount = $this->dataRepository->getCountByBotId($botId);
            $byteCount = $this->dataRepository->getByteCountByBotId($botId);

            $stat = [
                'bot_id' => $botId,
                'name' => $setting->getName(),
                'last_started' => $launch?->getStartTime(),
                'last_finished' => $launch?->getEndTime() ?? 'n/a',
                'total_records' => $recordsCount,
                'total_launches' => $launchCount,
                'total_bytes' => $byteCount,
            ];
            $stats[] = $stat;
        }

        return $this->render('stats/index.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/stats/graphs/{botId}', name: 'app_robot_graphs')]
    public function graphs(ManagerRegistry $doctrine, ChartBuilderInterface $chartBuilder, int $botId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$doctrine->getRepository(RobotSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw $this->createAccessDeniedException('User does not own bot.');
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);

        $dates = Dates::lastWeekArray();


        foreach ($dates as $i => $date) {
            $dates[$i]['totalRecords'] =
                $this->dataRepository->getCountByBotIdAndDate($botId, $date['date']);
            $dates[$i]['totalLaunches'] =
                $this->launchesRepository->getCountByBotIdAndDate($botId, $date['date']);
        }

        $chart->setData([
            'labels' => array_column($dates, 'date'),
            'datasets' => [
                [
                    'label' => 'Total Records',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => array_column($dates, 'totalRecords'),
                ],
                [
                    'label' => 'Total Launches',
                    'backgroundColor' => 'rgb(99, 255, 132)',
                    'borderColor' => 'rgb(99, 255, 132)',
                    'data' => array_column($dates, 'totalLaunches'),
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $this->render('stats/graphs.html.twig', [
            'chart' => $chart,
        ]);
    }
}
