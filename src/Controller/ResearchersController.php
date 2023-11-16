<?php

namespace App\Controller;

use App\Entity\Researchers;
use App\Repository\ResearchersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class ResearchersController extends AbstractController
{
    #[Route('/Researchers', name: 'app_researchers', methods: ['GET'])]
    public function index(ResearchersRepository $researchersRepository, Request $request): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $researchers = $researchersRepository->findBy($queryParams);
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
    public function show(ResearchersRepository $researcherRepository, Researchers $researcher): JsonResponse
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

    #[Route('/Researchers/patch/{id}', name: 'app_researchers_update', methods: ['PATCH'])]
    public function update(ResearchersRepository $researcherRepository, Researchers $researcher, Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
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
}