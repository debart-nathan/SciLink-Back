<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\ResponseError;
use App\Entity\ResearchCenters;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ResearchCentersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResearchCentersController extends AbstractController
{
    #[Route('/ResearchCenters', name: 'app_researchCenters', methods: ['GET'])]
    public function index(
        ResearchCentersRepository $researchCentersRepository,
        Request $request
    ): JsonResponse {
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
    public function show(
        ResearchCentersRepository $researchCenterRepository,
        ResearchCenters $researchCenter
    ): JsonResponse {
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


    #[Route('/ResearchCenters/{id}/patch', name: 'app_ResearchCenters_update', methods: ['PATCH'])]
    public function update(
        ResearchCentersRepository $researchCenterRepository,
        ResearchCenters $researchCenter,
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
        if ($loginUser->getId() !== $researchCenter->getId()) {
            return new JsonResponse($responseError);
        }
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

    #[Route('/ResearchCenters/create/post', name: 'app_researchCenters_create', methods: ['POST'])]
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
        }
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        //todo : faire que que l'administrateur peut ajouter un centre de recherche

        // Créer un nouvel objet ResearchCenters
        $researchCenter = new ResearchCenters();

        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier les données nécessaires
        if (
            !isset($data['label']) ||
            !isset($data['sigle']) ||
            !isset($data['founding_year']) ||
            !isset($data['is_active'])
        ) {
            return new JsonResponse(['error' => 'Tous les champs sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        // Attribuer les données à l'objet ResearchCenters
        $researchCenter->setLibelle($data['label']);
        $researchCenter->setSigle($data['sigle']);
        $researchCenter->setFoundingYear($data['founding_year']);
        $researchCenter->setIsActive($data['is_active']);
        $researchCenter->setWebsite($data['website']);
        $researchCenter->setFicheMsr($data['fiche_msr']);

        // Ajouter le nouvel objet à la base de données
        $entityManager->persist($researchCenter);
        $entityManager->flush();

        // Retourner la réponse JSON avec les détails du nouvel objet créé
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
        return new JsonResponse($researchCenterJson, Response::HTTP_CREATED, [], true);
    }
}