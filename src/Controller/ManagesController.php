<?php

namespace App\Controller;

use App\Entity\Manages;
use App\Repository\ManagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ManagesController extends AbstractController
{
    #[Route('/Manages', name: 'app_manages', methods: ['GET'])]
    public function index(ManagesRepository $managesRepository, Request $request): JsonResponse
    {
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
                'Grade' => $manage->getGrade(),
                'Personnel' => $manage->getPersonnel()->getId(),
                'ResearchCenter' => $manage->getResearchCenter()->getId(),
            ];
        }
        $managesJson = json_encode($managesArray);
        return new JsonResponse($managesJson, 200, [], true);
    }

    #[Route('/Manages/{id}', name: 'app_manages_show', methods: ['GET'])]
    public function show(ManagesRepository $manageRepository, Manages $manage): JsonResponse
    {
        $manageArray = [
            'id' => $manage->getId(),
            'Grade' => $manage->getGrade(),
            'Personnel' => $manage->getPersonnel()->getId(),
            'ResearchCenter' => $manage->getResearchCenter()->getId(),
        ];
        $manageJson = json_encode($manageArray);
        return new JsonResponse($manageJson, 200, [], true);
    }

    #[Route('/Manages/{id}', name: 'app_manages_update', methods: ['PATCH'])]
    public function update(ManagesRepository $manageRepository, Manages $manage, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['Grade'])) {
            $manage->setGrade($data['Grade']);
        }
        if (isset($data['Personnel'])) {
            $manage->setPersonnel($data['Personnel']);
        }
        if (isset($data['ResearchCenter'])) {
            $manage->setResearchCenter($data['ResearchCenter']);
        }

        $entityManager->persist($manage);
        $entityManager->flush();

        $manageArray = [
            'id' => $manage->getId(),
            'Grade' => $manage->getGrade(),
            'Personnel' => $manage->getPersonnel()->getId(),
            'ResearchCenter' => $manage->getResearchCenter()->getId(),
        ];
        $manageJson = json_encode($manageArray);
        return new JsonResponse($manageJson, 200, [], true);
    }
}