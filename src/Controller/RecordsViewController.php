<?php

namespace App\Controller;

use App\Entity\RobotSettings;
use App\Entity\RobotData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

        $fileName = $record->getId();
        $blob = $record->getDataStream();

        $response = new Response();
        $response->headers->set('Content-Type', $record->getContentType());
        $response->headers->set('Content-Length', strlen($blob));
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $fileName .'"');
        $response->setContent($blob);

        return $response;
    }
}
