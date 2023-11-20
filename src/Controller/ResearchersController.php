<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Researchers;
use App\Service\ResponseError;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ResearchersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResearchersController extends AbstractController
{
    #[Route('/Researchers', name: 'app_researchers', methods: ['GET'])]
    public function index(
        ResearchersRepository $researchersRepository,
        Request $request,
        UsersRepository $usersRepository
        ): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            if (isset($queryParams['app_user'])) {
                $user = $usersRepository->find($queryParams['app_user']);
                if (isset($user)) {
                    $researchers = [$user->getResearcher()];
                } else {
                    $researchers = [];
                }
            }
        } else {
            $researchers = $researchersRepository->findAll();
        }
        $researchersArray = [];
        foreach ($researchers as $researcher) {
            $user_id = $researcher->getUser() ? $researcher->getUser()->getId() : null;
            $researchersArray[] = [
                'id' => $researcher->getId(),
                'description' => $researcher->getDescription(),
                'user_id' => $user_id,
            ];
        }
        $researchersJson = json_encode($researchersArray);
        return new JsonResponse($researchersJson, 200, [], true);
    }

    #[Route('/Researchers/{id}', name: 'app_researchers_show', methods: ['GET'])]
    public function show(
        ResearchersRepository $researcherRepository,
        Researchers $researcher
        ): JsonResponse
    {
        $user_id = $researcher->getUser() ? $researcher->getUser()->getId() : null;
        $researcherArray = [
            'id' => $researcher->getId(),
            'description' => $researcher->getDescription(),
            'user_id' => $user_id,
        ];
        $researcherJson = json_encode($researcherArray);
        return new JsonResponse($researcherJson, 200, [], true);
    }


    #[Route('/Researchers/{id}/patch', name: 'app_researchers_update', methods: ['PATCH'])]
    public function update(
        ResearchersRepository $researcherRepository,
        Researchers $researcher, Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError
        ): JsonResponse

    {
        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!($token && ($loginUser->getId() === $researcher->getUser()->getId()))) {
            return new JsonResponse($responseError);
        }
        $data = json_decode($request->getContent(), true);

        if (isset($data['description'])) {
            $researcher->setDescription($data['description']);
        }

        $entityManager->persist($researcher);
        $entityManager->flush();

        $user_id = $researcher->getUser() ? $researcher->getUser()->getId() : null;
        $researcherArray = [
            'id' => $researcher->getId(),
            'description' => $researcher->getDescription(),
            'user_id' => $user_id,
        ];
        $researcherJson = json_encode($researcherArray);
        return new JsonResponse($researcherJson, 200, [], true);
    }

    #[Route('/Researchers/create/post', name: 'app_researchers_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError,
        UsersRepository $usersRepository
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        // Vérifier si l'utilisateur est authentifié
        if (!$token) {
            return new JsonResponse($responseError);
        }

        /** @var Users $loginUser */
        $loginUser = $token->getUser();

        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier les données nécessaires
        if (!isset($data['description']) || !isset($data['user_id'])) {
            return new JsonResponse(
                ['error' => 'La description et l\'ID utilisateur sont requis.']
                , Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'utilisateur existe
        $user = $usersRepository->find($data['user_id']);
        if (!$user) {
            return new JsonResponse(
                ['error' => 'L\'utilisateur fourni n\'existe pas.']
                , Response::HTTP_NOT_FOUND);
        }

        // Créer un nouvel objet Researchers
        $researcher = new Researchers();

        // Attribuer les données à l'objet Researchers
        $researcher->setDescription($data['description']);
        $researcher->setUser($user);

        // Ajouter le nouvel objet à la base de données
        $entityManager->persist($researcher);
        $entityManager->flush();

        // Retourner la réponse JSON avec les détails du nouvel objet créé
        $researcherArray = [
            'id' => $researcher->getId(),
            'description' => $researcher->getDescription(),
            'user_id' => $user->getId(),
        ];

        $researcherJson = json_encode($researcherArray);

        return new JsonResponse($researcherJson, Response::HTTP_CREATED, [], true);
    }

    #[Route('/Researchers/{id}/delete', name: 'delete_researcher', methods: ['DELETE'])]
    public function deleteRechercher(int $id, EntityManagerInterface $entityManager, Researchers $researcher,ResearchersRepository $researcherRepository,): JsonResponse
{

        $researcher = $researcherRepository->find($id);
        if (!$researcher) {
            throw $this->createNotFoundException('User not found');
        }
        $entityManager->remove($researcher);
        $entityManager->flush();

        return new JsonResponse(['status' => 'researcher deleted'], 200);
}

}