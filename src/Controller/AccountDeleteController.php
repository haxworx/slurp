<?php

namespace App\Controller;

use App\Entity\RobotSettings;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class AccountDeleteController extends AbstractController
{
    #[Route('/account/delete', name: 'app_account_delete')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('account-delete', $token)) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }

        // Remove all user-associated data from hereon.

        $botIds = $doctrine->getRepository(RobotSettings::class)->findAllBotIdsByUserId($userId);

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
