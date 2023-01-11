<?php

namespace App\Controller;

use App\Entity\RobotSettings;
use App\Entity\RobotData;
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
    #[Route('/records/download/{botId}/record/{recordId}', name: 'app_records_download')]
    public function download(Request $request, ManagerRegistry $doctrine, int $botId, int $recordId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

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

        $fileName = $record->getId();

        $response = new Response($record->getDataStream());

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName . '.' . $ext,
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
