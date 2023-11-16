<?php

namespace App\Controller;

use App\Entity\RelationStatus;
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
    public function index(RelationStatusRepository $relationStatusRepository, Request $request): JsonResponse
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
    public function show(RelationStatusRepository $relationStatusRepository, RelationStatus $relationStatus): JsonResponse
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
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!($token && ($token->getUser()->getId() === $user->getId()))) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_UNAUTHORIZED);
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
}
