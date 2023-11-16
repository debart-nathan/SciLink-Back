<?php

namespace App\Controller;

use App\Entity\Personnels;
use App\Repository\PersonnelsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PersonnelsController extends AbstractController
{
    #[Route('/Personnels', name: 'app_personnels', methods: ['GET'])]
    public function index(PersonnelsRepository $personnelsRepository, Request $request): JsonResponse
    {
        if ($request->query->count() > 0) {
            $queryParams = $request->query->all();
            $personnels = $personnelsRepository->findBy($queryParams);
        } else {
            $personnels = $personnelsRepository->findAll();
        }
        $personnelsArray = [];
        foreach ($personnels as $personnel) {
            $personnelsArray[] = [
                'id' => $personnel->getId(),
                'first_name' => $personnel->getFirstName(),
                'last_name' => $personnel->getlastName(),
                // 'manages' => $personnel->getManages(),
            ];
        }
        $personnelsJson = json_encode($personnelsArray);
        return new JsonResponse($personnelsJson, 200, [], true);
    }

    #[Route('/Personnels/{id}', name: 'app_personnels_show', methods: ['GET'])]
    public function show(PersonnelsRepository $personnelRepository, Personnels $personnel): JsonResponse
    {
        $personnelArray = [
            'id' => $personnel->getId(),
            'first_name' => $personnel->getFirstName(),
            'last_name' => $personnel->getLastName(),
            // 'manages' => $personnel->getManages(),
        ];
        $personnelJson = json_encode($personnelArray);
        return new JsonResponse($personnelJson, 200, [], true);
    }

    #[Route('/Personnels/{id}', name: 'app_personnels_update', methods: ['PATCH'])]
    public function update(PersonnelsRepository $personnelRepository, Personnels $personnel, Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!($token && ($token->getUser()->getId() === $user->getId()))) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);

        if (isset($data['first_name'])) {
            $personnel->setFirstName($data['first_name']);
        }
        if (isset($data['last_name'])) {
            $personnel->setLastName($data['last_name']);
        }

        $entityManager->persist($personnel);
        $entityManager->flush();

        $personnelArray = [
            'id' => $personnel->getId(),
            'first_name' => $personnel->getFirstName(),
            'last_name' => $personnel->getLastName(),
            // 'manages' => $personnel->getManages(),
        ];
        $personnelJson = json_encode($personnelArray);
        return new JsonResponse($personnelJson, 200, [], true);
    }
}