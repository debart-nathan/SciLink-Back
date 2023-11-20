<?php

namespace App\Controller;

use App\Entity\RelationStatus;
use App\Entity\Users;
use App\Service\ResponseError;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\RelationStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RelationStatusController extends AbstractController
{
    #[Route('/RelationStatus', name: 'app_relation_status', methods: ['GET'])]
    public function index(
        RelationStatusRepository $relationStatusRepository,
        Request $request
        ): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $relationStatus = $relationStatusRepository->findBy($queryParams);
        } else {
            $relationStatus = $relationStatusRepository->findAll();
        }
        $relationStatusArray = [];
        foreach ($relationStatus as $relationStatu) {
            $relationStatusArray[] = [
                'id' => $relationStatu->getId(),
                'status' => $relationStatu->getStatus(),
            ];
        }
        $relationStatusJson = json_encode($relationStatusArray);
        return new JsonResponse($relationStatusJson, 200, [], true);

    }

    #[Route('/RelationStatus/{id}', name: 'app_relation_status_show', methods: ['GET'])]
    public function show(
        RelationStatusRepository $relationStatusRepository,
        RelationStatus $relationStatus
        ): JsonResponse
    {
        $relationStatusArray = [
            'id' => $relationStatus->getId(),
            'status' => $relationStatus->getStatus(),
        ];
        $relationStatusJson = json_encode($relationStatusArray);
        return new JsonResponse($relationStatusJson, 200, [], true);
    }


    #[Route('/RelationStatus/{id}/patch', name: 'app_relation_status_update', methods: ['PATCH'])]
    public function update(
        RelationStatusRepository $relationStatusRepository,
        RelationStatus $relationStatus,
        Request $request,
        EntityManagerInterface $entityManager,
        ResponseError $responseError,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // Check if the user is an admin
        if (!in_array('ROLE_ADMIN', $loginUser->getRoles())) {
            return new JsonResponse($responseError);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['status'])) {
            $relationStatus->setStatus($data['status']);
        }
        $entityManager->persist($relationStatus);
        $entityManager->flush();
        $relationStatusJson = json_encode($relationStatus);
        return new JsonResponse($relationStatusJson, 200, [], true);
    }
    #[Route('/RelationStatus/create/post', name: 'app_relation_status_create', methods: ['POST'])]
    public function create(
        RelationStatusRepository $relationStatusRepository,
        RelationStatus $relationStatus,
        Request $request,
        EntityManagerInterface $entityManager,
        ResponseError $responseError,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // Check if the user is an admin
        if (!in_array('ROLE_ADMIN', $loginUser->getRoles())) {
            return new JsonResponse($responseError);
        }

        // Créer un nouvel objet RelationStatus
        $relationStatus = new RelationStatus();

        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier les données nécessaires
        if (!isset($data['status'])) {
            return new JsonResponse(['error' => 'Le champ status est requis.'], Response::HTTP_BAD_REQUEST);
        }

        // Attribuer les données à l'objet RelationStatus
        $relationStatus->setStatus($data['status']);

        // Ajouter le nouvel objet à la base de données
        $entityManager->persist($relationStatus);
        $entityManager->flush();

        // Retourner la réponse JSON avec les détails du nouvel objet créé
        $relationStatusArray = [
            'id' => $relationStatus->getId(),
            'status' => $relationStatus->getStatus(),
        ];

        $relationStatusJson = json_encode($relationStatusArray);

        return new JsonResponse($relationStatusJson, Response::HTTP_CREATED, [], true);
    }
}
