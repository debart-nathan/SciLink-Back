<?php

namespace App\Controller;

use App\Entity\ResearchCenters;
use App\Repository\ResearchCentersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ResearchCentersController extends AbstractController
{
    #[Route('/ResearchCenters', name: 'app_researchCenters', methods: ['GET'])]
    public function index(ResearchCentersRepository $researchCentersRepository, Request $request): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $researchCenters = $researchCentersRepository->findBy($queryParams);
        } else {
            $researchCenters = $researchCentersRepository->findAll();
        }
        $researchCentersArray = [];
        foreach ($researchCenters as $researchCenter) {
            //$user_id = $researchCenter->getAppUser() ? $researchCenter->getAppUser()->getId() : null;
            $researchCentersArray[] = [
                'id' => $researchCenter->getId(),
                'label' => $researchCenter->getLibelle(),
                'sigle' => $researchCenter->getSigle(),
                'founding_year' => $researchCenter->getFoundingYear(),
                'is_active' => $researchCenter->isIsActive(),
                'website' => $researchCenter->getWebsite(),
                'fiche_msr' => $researchCenter->getFicheMsr(),
            ];
        }
        $researchCentersJson = json_encode($researchCentersArray);
        return new JsonResponse($researchCentersJson, 200, [], true);
    }

    #[Route('/ResearchCenters/{id}', name: 'app_ResearchCenters_show', methods: ['GET'])]
    public function show(ResearchCentersRepository $researchCenterRepository, ResearchCenters $researchCenter): JsonResponse
    {
        $researchCenterArray = [
            'id' => $researchCenter->getId(),
            'label' => $researchCenter->getLibelle(),
            'sigle' => $researchCenter->getSigle(),
            'founding_year' => $researchCenter->getFoundingYear(),
            'is_active' => $researchCenter->isIsActive(),
            'website' => $researchCenter->getWebsite(),
            'fiche_msr' => $researchCenter->getFicheMsr(),

        ];
        $researchCenterJson = json_encode($researchCenterArray);
        return new JsonResponse($researchCenterJson, 200, [], true);
    }

    #[Route('/ResearchCenters/patch/{id}', name: 'app_ResearchCenters_update', methods: ['PATCH'])]
    public function update(ResearchCentersRepository $researchCenterRepository, ResearchCenters $researchCenter, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['label'])) {
            $researchCenter->setLibelle($data['label']);
        }
        if (isset($data['sigle'])) {
            $researchCenter->setSigle($data['sigle']);
        }
        if (isset($data['founding_year'])) {
            $researchCenter->setFoundingYear($data['founding_year']);
        }
        if (isset($data['is_active'])) {
            $researchCenter->setIsActive($data['is_active']);
        }
        if (isset($data['website'])) {
            $researchCenter->setWebsite($data['website']);
        }
        if (isset($data['fiche_msr'])) {
            $researchCenter->setFicheMsr($data['fiche_msr']);
        }

        $entityManager->persist($researchCenter);
        $entityManager->flush();


        $researchCenterArray = [
            'id' => $researchCenter->getId(),
            'label' => $researchCenter->getLibelle(),
            'sigle' => $researchCenter->getSigle(),
            'founding_year' => $researchCenter->getFoundingYear(),
            'is_active' => $researchCenter->isIsActive(),
            'website' => $researchCenter->getWebsite(),
            'fiche_msr' => $researchCenter->getFicheMsr(),

        ];
        $researchCenterJson = json_encode($researchCenterArray);
        return new JsonResponse($researchCenterJson, 200, [], true);
    }
}