<?php

namespace App\Controller;

use App\Entity\Personnels;
use App\Entity\Users;
use App\Service\ResponseError;
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
    public function index(
        PersonnelsRepository $personnelsRepository,
        Request $request
    ): JsonResponse {
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
    public function show(
        PersonnelsRepository $personnelRepository,
        Personnels $personnel
    ): JsonResponse {
        $personnelArray = [
            'id' => $personnel->getId(),
            'first_name' => $personnel->getFirstName(),
            'last_name' => $personnel->getLastName(),
            // 'manages' => $personnel->getManages(),
        ];
        $personnelJson = json_encode($personnelArray);
        return new JsonResponse($personnelJson, 200, [], true);
    }

    #[Route('/Personnels/{id}/patch', name: 'app_personnels_update', methods: ['PATCH'])]
    public function update(
        PersonnelsRepository $personnelRepository,
        Personnels $personnel,
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError
    ): JsonResponse {

        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!$token) {
            return new JsonResponse($responseError);
        }
        if ($loginUser->getId() !== $personnel->getId()) {
            return new JsonResponse($responseError);
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

    #[Route('/Personnels/create/post', name: 'app_personnels_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        // Vérifier si l'utilisateur est authentifié
        if (!$token) {
            return new JsonResponse($responseError);
        } //TODO ... finir la verificación de l'utilisateur

        // Créer un nouvel objet Personnels
        $personnel = new Personnels();

        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier les données nécessaires
        if (!isset($data['first_name']) || !isset($data['last_name'])) {
            return new JsonResponse(
                ['error' => 'Les champs Prenom et Nom sont requis.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Attribuer les données à l'objet Personnels
        $personnel->setFirstName($data['first_name']);
        $personnel->setLastName($data['last_name']);

        // Ajouter le nouvel objet à la base de données
        $entityManager->persist($personnel);
        $entityManager->flush();


        // Retourner la réponse JSON avec les détails du nouvel objet créé
        $personnelArray = [
            'id' => $personnel->getId(),
            'first_name' => $personnel->getFirstName(),
            'last_name' => $personnel->getLastName(),
        ];

        $personnelJson = json_encode($personnelArray);

        return new JsonResponse($personnelJson, Response::HTTP_CREATED, [], true);
    }

    #[Route('/Personnels/{id}/delete', name: 'delete_personnel', methods: ['DELETE'])]
    public function deletePersonnel(int $id, EntityManagerInterface $entityManager, PersonnelsRepository $personnelsRepository, Personnels $personnel): JsonResponse
    {

        $personnel = $personnelsRepository->find($id);

        if (!$personnel) {
            throw $this->createNotFoundException('Personnel not found');
        }
        $entityManager->remove($personnel);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Personnel deleted'], 200);
    }
}
