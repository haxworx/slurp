<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) Al Poole <netstar@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Repository\RobotDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET', 'POST'])]
    public function index(Request $request, RobotDataRepository $dataRepository): Response
    {
        $count = 0;
        $paginator = null;

        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $searchTerm = $request->get('search') ?? null;
        $latest = $request->get('latest') ?? null;
        $offset = $request->get('offset') ?? 0;

        if ($searchTerm) {
            $newerFirst = ('on' === $latest) ? true : false;
            $paginator = $dataRepository->getSearchPaginator($searchTerm, $offset, $newerFirst, $user->getId());
            $count = count($paginator);
        }

        return $this->render('search/index.html.twig', [
            'next' => min($count, $offset + RobotDataRepository::PAGINATOR_PER_PAGE),
            'prev' => $offset - RobotDataRepository::PAGINATOR_PER_PAGE,
            'records' => $paginator,
            'search_term' => $searchTerm,
            'latest' => $latest,
            'count' => $count,
        ]);
    }
}
