<?php

// src/Controller/SearchController.php

namespace App\Controller;

use App\Repository\RobotDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET', 'POST'])]
    public function index(Request $request, RobotDataRepository $recordsRepository): Response
    {
        $count = 0;
        $paginator = null;

        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $searchTerm = $request->get('search') ?? null;
        $offset = $request->get('offset') ?? 0;

        if ($searchTerm) {
            $paginator = $recordsRepository->getSearchPaginator($searchTerm, $offset, $user->getId());
            $count = count($paginator);
        }

        return $this->render('search/index.html.twig', [
            'next' => min($count, $offset + RobotDataRepository::PAGINATOR_PER_PAGE),
            'prev' => $offset - RobotDataRepository::PAGINATOR_PER_PAGE,
            'records' => $paginator,
            'search_term' => $searchTerm,
            'count' => $count,
        ]);
    }
}
