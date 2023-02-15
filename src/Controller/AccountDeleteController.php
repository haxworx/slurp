<?php

// src/Controller/AccountDeleteController.php

namespace App\Controller;

use App\Entity\RobotData;
use App\Entity\RobotLaunches;
use App\Entity\RobotLog;
use App\Entity\RobotSettings;
use App\Service\AppLogger;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountDeleteController extends AbstractController
{
    #[Route('/account/delete', name: 'app_account_delete')]
    public function index(Request $request, ManagerRegistry $doctrine, AppLogger $logger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('account-delete', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // Remove all user-associated data from hereon.

        $logger->info("account deleted", ['user_id' => $user->getId()]);

        $botIds = $doctrine->getRepository(RobotSettings::class)->findAllBotIdsByUserId($user->getId());

        foreach ($botIds as $botId) {
            $doctrine->getRepository(RobotData::class)->deleteAllByBotId($botId);
            $doctrine->getRepository(RobotLog::class)->deleteAllByBotId($botId);
            $doctrine->getRepository(RobotLaunches::class)->deleteAllByBotId($botId);
        }

        $doctrine->getRepository(RobotSettings::class)->deleteAllByUserId($user->getId());

        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $request->getSession()->invalidate();
        $this->container->get('security.token_storage')->setToken(null);

        return $this->redirectToRoute('app_dashboard');
    }
}
