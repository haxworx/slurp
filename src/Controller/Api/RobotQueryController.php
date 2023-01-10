<?php

namespace App\Controller\Api;

use App\Entity\RobotSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Persistence\ManagerRegistry;

class RobotQueryController extends AbstractController
{
    private $serializer = null;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/api/robot/query/all', name: 'app_api_robot_query')]
    public function queryAll(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $robots = $doctrine->getRepository(RobotSettings::class)->findAllByUserId($user->getId());
        $jsonContent = $this->serializer->serialize($robots, 'json');

        $response = new JsonResponse();
        $response->setContent($jsonContent);

        return $response; 
    }
}