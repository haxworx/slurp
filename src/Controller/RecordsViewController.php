<?php

// src/Controller/RecordsViewController.php

namespace App\Controller;

use App\Entity\RobotSettings;
use App\Entity\RobotData;
use App\Entity\RobotLaunches;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Mime\MimeTypes;
use Doctrine\Persistence\ManagerRegistry;

class RecordsViewController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/records/download/{botId}/record/{recordId}', name: 'app_records_download')]
    public function download(Request $request, int $botId, int $recordId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $doctrine = $this->doctrine;

        if (!$doctrine->getRepository(RobotSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new AccessDeniedException(
                'User does not own bot.'
            );
        }

        $record = $doctrine->getRepository(RobotData::class)->findOneById($recordId);
        if (!$record) {
            throw $this->createNotFoundException(
                'No record found.'
            );
        }

        // Determine our file extension.
        $ext = "txt";
        $mimeTypes = new MimeTypes();
        $exts = $mimeTypes->getExtensions($record->getContentType());
        if (count($exts) !== 0) {
           $ext = $exts[0];
        }

        $fileName = basename($record->getPath()) . $record->getId();

        $response = new Response($record->getDataStream());

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName . '.' . $ext,
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

        $dom = new \DomDocument;
        @$dom->loadHTML($data);

        $this->replaceTag($dom, "link", "href", $botId, $recordId, $launchId);
        $this->replaceTag($dom, "a", "href", $botId, $recordId, $launchId);
        $this->replaceTag($dom, "img", "src", $botId, $recordId, $launchId);

        return $dom->saveHTML();
    }

    #[Route('/records/view/{botId}/launch/{launchId}/record/{recordId}', name: 'app_records_view')]
    public function view(Request $request, int $botId, int $recordId, int $launchId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $doctrine = $this->doctrine;

        if (!$doctrine->getRepository(RobotSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new AccessDeniedException(
                'User does not own bot.'
            );
        }

        $record = $doctrine->getRepository(RobotData::class)->findOneById($recordId);
        if (!$record) {
            throw $this->createNotFoundException(
                'No record found.'
            );
        }

        $data = $record->getDataStream();

        // Attempt to generate locally traversible and visually identical view
        // of the web page. Replace links and CSS with copies stored locally
        // if possible.
        if ($record->getContentType() === "text/html") {
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
            throw new AccessDeniedException(
                'User does not own bot.'
            );
        }

        $settings = $doctrine->getRepository(RobotSettings::class)->findOneByUserIdAndBotId($userId, $botId);
        if (!$settings) {
            throw $this->createNotFoundException(
                'No robot for id: ' . $botId
            );
        }
    
        $launch = $doctrine->getRepository(RobotLaunches::class)->findOneByLaunchId($launchId);
        if (!$launch) {
            throw $this->createNotFoundException(
                'No launch for id: ' . $launchId
            );
        }

        $duration = $launch->getStartTime()->format('Y-m-d H:i:s') . ' to ' .
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
            'address' => $settings->getScheme() . "://" . $settings->getDomainName(),
            'duration' => $duration,
        ]);
    }
}
