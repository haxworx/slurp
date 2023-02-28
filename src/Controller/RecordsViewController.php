<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) ai poole <imabiggeek@slurp.ai>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\RobotData;
use App\Entity\RobotLaunches;
use App\Entity\RobotSettings;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Annotation\Route;

class RecordsViewController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/records/download/{botId}/record/{recordId}', name: 'app_records_download')]
    public function download(Request $request, int $botId, int $recordId): Response
    {
        $record = $this->getRecordByBotIdAndId($botId, $recordId);

        // Determine our file extension.
        $ext = 'txt';
        $mimeTypes = new MimeTypes();
        $exts = $mimeTypes->getExtensions($record->getContentType());
        if (0 !== count($exts)) {
            $ext = $exts[0];
        }

        $fileName = basename($record->getPath()).$record->getId();

        $response = new Response($record->getDataStream());

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName.'.'.$ext,
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function replaceTag(\DOMDocument $dom, string $tag, string $attr, int $botId, int $recordId, int $launchId): void
    {
        $links = $dom->getElementsByTagName($tag);
        foreach ($links as $link) {
            $value = $link->getAttribute($attr);
            $recordId = $this->doctrine->getRepository(RobotData::class)->findRecordIdByLaunchIdAndPath($launchId, $value);
            if ($recordId) {
                $link->setAttribute($attr, sprintf('/records/view/%d/launch/%d/record/%d', $botId, $launchId, $recordId));
            }
        }
    }

    private function replaceTags(string $data, int $botId, int $recordId, int $launchId): string
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($data);

        $this->replaceTag($dom, 'link', 'href', $botId, $recordId, $launchId);
        $this->replaceTag($dom, 'a', 'href', $botId, $recordId, $launchId);
        $this->replaceTag($dom, 'img', 'src', $botId, $recordId, $launchId);

        return $dom->saveHTML();
    }

    #[Route('/records/view/{botId}/launch/{launchId}/record/{recordId}', name: 'app_records_view')]
    public function view(Request $request, int $botId, int $recordId, int $launchId): Response
    {
        $record = $this->getRecordByBotIdAndId($botId, $recordId);

        $data = $record->getDataStream();

        // Attempt to generate locally traversable and visually identical view
        // of the web page. Replace links and CSS with copies stored locally
        // if possible.
        if ('text/html' === $record->getContentType()) {
            $data = $this->replaceTags($data, $botId, $recordId, $launchId);
        }

        $response = new Response($data);
        $response->headers->set('Content-Type', $record->getContentType());

        return $response;
    }

    #[Route('/records', name: 'app_records')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('records_list/index.html.twig');
    }

    #[Route('/records/list/{botId}/launch/{launchId}/offset/{offset}', name: 'app_records_list')]
    public function recordsList(Request $request, int $botId, int $launchId, int $offset): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userId = $this->getUser()->getId();
        $doctrine = $this->doctrine;

        if (!$doctrine->getRepository(RobotSettings::class)->userOwnsBot($userId, $botId)) {
            throw $this->createAccessDeniedException('User does not own bot.');
        }

        $bot = $doctrine->getRepository(RobotSettings::class)->findOneByUserIdAndBotId($userId, $botId);
        if (!$bot) {
            throw $this->createNotFoundException('No robot for id: '.$botId);
        }

        $launch = $doctrine->getRepository(RobotLaunches::class)->findOneById($launchId);
        if (!$launch) {
            throw $this->createNotFoundException('No launch for id: '.$launchId);
        }

        $duration = $launch->getStartTime()->format('Y-m-d H:i:s').' to '.
                    $launch->getEndTime()?->format('Y-m-d H:i:s');

        $repository = $doctrine->getRepository(RobotData::class);
        $paginator = $doctrine->getRepository(RobotData::class)->getPaginator($launchId, $offset);
        $n = count($paginator);

        return $this->render('records_list/view.html.twig', [
            'records' => $paginator,
            'next' => min($n, $offset + $repository::PAGINATOR_PER_PAGE),
            'prev' => $offset - $repository::PAGINATOR_PER_PAGE,
            'count' => $n,
            'bot_id' => $botId,
            'launch_id' => $launchId,
            'address' => $bot->getScheme().'://'.$bot->getDomainName(),
            'duration' => $duration,
        ]);
    }

    public function getRecordByBotIdAndId(int $botId, int $recordId): RobotData
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $doctrine = $this->doctrine;

        if (!$doctrine->getRepository(RobotSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw $this->createAccessDeniedException('User does not own bot.');
        }

        $record = $doctrine->getRepository(RobotData::class)->findOneById($recordId);
        if (!$record) {
            throw $this->createNotFoundException('No record found.');
        }

        return $record;
    }
}
