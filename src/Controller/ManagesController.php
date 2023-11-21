<?php

namespace App\Controller;

use App\Entity\Manages;
use App\Entity\Users;
use App\Repository\ManagesRepository;
use App\Service\ResponseError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ManagesController extends AbstractController
{
    #[Route('/Manages', name: 'app_manages', methods: ['GET'])]
    public function index(
        ManagesRepository $managesRepository,
        Request $request
    ): JsonResponse {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $manages = $managesRepository->findBy($queryParams);
        } else {
            $manages = $managesRepository->findAll();
        }
        $managesArray = [];
        foreach ($manages as $manage) {
            $managesArray[] = [
                'id' => $manage->getId(),
                'grade' => $manage->getGrade(),
                'personnel_id' => $manage->getPersonnel()->getId(),
                'research_center_id' => $manage->getResearchCenter()->getId(),
            ];
        }
        $managesJson = json_encode($managesArray);
        return new JsonResponse($managesJson, 200, [], true);
    }

    #[Route('/Manages/{id}', name: 'app_manages_show', methods: ['GET'])]
    public function show(
        ManagesRepository $manageRepository,
        Manages $manage
    ): JsonResponse {
        $manageArray = [
            'id' => $manage->getId(),
            'grade' => $manage->getGrade(),
            'personnel_id' => $manage->getPersonnel()->getId(),
            'research_center_id' => $manage->getResearchCenter()->getId(),
        ];
        $manageJson = json_encode($manageArray);
        return new JsonResponse($manageJson, 200, [], true);
    }


    #[Route('/Manages/{id}/patch', name: 'app_manages_update', methods: ['PATCH'])]
    public function update(
        ManagesRepository $manageRepository,
        Manages $manage,
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError,
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!$token) {
            return new JsonResponse($responseError);
        }
        /** @var Users $loginUser */
        $loginUser = $token->getUser();

        if (!$loginUser->getId() === $manage->getResearchCenter()->getId()) {

            return new JsonResponse($responseError);
        }
        $data = json_decode($request->getContent(), true);

        if (isset($data['grade'])) {
            $manage->setGrade($data['grade']);
        }
        if (isset($data['personnel_id'])) {
            $manage->setPersonnel($data['personnel_id']);
        }
        if (isset($data['research_center_id'])) {
            $manage->setResearchCenter($data['research_center_id']);
        }

        $entityManager->persist($manage);
        $entityManager->flush();

        $manageArray = [
            'id' => $manage->getId(),
            'grade' => $manage->getGrade(),
            'personnel_id' => $manage->getPersonnel()->getId(),
            'research_center_id' => $manage->getResearchCenter()->getId(),
        ];
        $manageJson = json_encode($manageArray);
        return new JsonResponse($manageJson, 200, [], true);
    }

    #[Route('/Manages', name: 'app_manages_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        if (!$token) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_UNAUTHORIZED);
        }
        /** @var Users $loginUser */
        $loginUser = $token->getUser();

        $data = json_decode($request->getContent(), true);

        $manage = new Manages();
        $manage->setGrade($data['grade']);

        // Assurez-vous d'ajuster cette logique selon vos besoins,
        //par exemple attribuer le personnel et le centre de recherche appropriés
        $manage->setPersonnel($data['personnel_id']);
        $manage->setResearchCenter($data['research_center_id']);

        if (!$loginUser->getId() === $manage->getResearchCenter()->getId()) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_UNAUTHORIZED);
        }

        $entityManager->persist($manage);
        $entityManager->flush();

        $manageArray = [
            'id' => $manage->getId(),
            'grade' => $manage->getGrade(),
            'personnel_id' => $manage->getPersonnel()->getId(),
            'research_center_id' => $manage->getResearchCenter()->getId(),
        ];
        $manageJson = json_encode($manageArray);
        return new JsonResponse($manageJson, Response::HTTP_CREATED, [], true);
    }

    #[Route('/Manages/{id}/delete', name: 'delete_manage', methods: ['DELETE'])]
    public function deleteManage(int $id, EntityManagerInterface $entityManager, ManagesRepository $managesRepository, Manages $manage): JsonResponse
    {

        $manage = $managesRepository->find($id);

        if (!$manage) {
            throw $this->createNotFoundException('Manage not found');
        }
        $entityManager->remove($manage);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Manage deleted'], 200);
    }
}
