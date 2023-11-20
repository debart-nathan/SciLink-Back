<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Tutelles;
use App\Service\ResponseError;
use App\Repository\TutellesRepository;
use App\Repository\InvestorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ResearchCentersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TutellesController extends AbstractController
{
    private $authorizationChecker;
    private $tokenStorage;
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
        )
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/Tutelles', name: 'app_tutelles', methods: ['GET'])]
    public function index(
        TutellesRepository $tutellesRepository,
        Request $request
        ): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $tutelles = $tutellesRepository->findBy($queryParams);
        } else {
            $tutelles = $tutellesRepository->findAll();
        }
        $tutellesArray = [];
        foreach ($tutelles as $tutelle) {

            $tutellesArray[] = [
                'id' => $tutelle->getId(),
                'uai' => $tutelle->getUai(),
                'siret' => $tutelle->getSiret(),
                'type' => $tutelle->getType(),
                'investor_id' => $tutelle->getInvestor()->getId(),
                'research_center_id' => $tutelle->getResearchCenter()->getId(),
            ];
        }
        $tutellesJson = json_encode($tutellesArray);
        return new JsonResponse($tutellesJson, 200, [], true);
    }

    #[Route('/Tutelles/{id}', name: 'app_tutelles_show', methods: ['GET'])]
    public function show(
        TutellesRepository $tutelleRepository,
        Tutelles $tutelle
        ): JsonResponse
    {

        $tutelleArray = [
            'id' => $tutelle->getId(),
            'uai' => $tutelle->getUai(),
            'siret' => $tutelle->getSiret(),
            'type' => $tutelle->getType(),
            'investor_id' => $tutelle->getInvestor()->getId(),
            'research_center_id' => $tutelle->getResearchCenter()->getId(),

        ];
        $tutelleJson = json_encode($tutelleArray);
        return new JsonResponse($tutelleJson, 200, [], true);
    }


    #[Route('/Tutelles/{id}/patch', name: 'app_tutelles_update', methods: ['PATCH'])]
    public function update(
        TutellesRepository $tutelleRepository,
        Tutelles $tutelle,
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError
        ): JsonResponse

    {
        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!($token && ($loginUser->getId() === $tutelle->getInvestor()->getId()))) {
            return new JsonResponse($responseError);
        }
        $data = json_decode($request->getContent(), true);

        if (isset($data['uai'])) {
            $tutelle->setUai($data['uai']);
        }
        if (isset($data['siret'])) {
            $tutelle->setSiret($data['siret']);
        }
        if (isset($data['type'])) {
            $tutelle->setType($data['type']);
        }
        if (isset($data['investor_id'])) {
            $tutelle->setInvestor($data['investor_id']);
        }
        if (isset($data['research_center_id'])) {
            $tutelle->setResearchCenter($data['research_center_id']);
        }

        $entityManager->persist($tutelle);
        $entityManager->flush();


        $tutelleArray = [
            'id' => $tutelle->getId(),
            'uai' => $tutelle->getUai(),
            'siret' => $tutelle->getSiret(),
            'type' => $tutelle->getType(),
            'investor_id' => $tutelle->getInvestor()->getId(),
            'research_center_id' => $tutelle->getResearchCenter()->getId(),

        ];
        $tutelleJson = json_encode($tutelleArray);
        return new JsonResponse($tutelleJson, 200, [], true);
    }

    #[Route('/Tutelles/create/post', name: 'app_tutelles_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError,
        InvestorsRepository $investorsRepository,
        ResearchCentersRepository $researchCentersRepository,


    ): JsonResponse {
        
        // Vérifier si l'utilisateur est authentifié
        
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN', $user)) {
            return new JsonResponse($responseError);
        }
        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier les données nécessaires
        if (
            !isset($data['uai']) ||
            !isset($data['siret']) ||
            !isset($data['type']) ||
            !isset($data['investor_id']) ||
            !isset($data['research_center_id'])
            ) {
            return new JsonResponse(['error' => 'Tous les champs sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'investisseur existe
        $investor = $investorsRepository->find($data['investor_id']);
        if (!$investor) {
            return new JsonResponse(
                ['error' => 'L\'investisseur avec l\'ID fourni n\'existe pas.'],
                Response::HTTP_NOT_FOUND);
        }

        // Vérifier si le centre de recherche existe
        $researchCenter = $researchCentersRepository->find($data['research_center_id']);
        if (!$researchCenter) {
            return new JsonResponse(
                ['error' => 'Le centre de recherche avec l\'ID fourni n\'existe pas.'],
                Response::HTTP_NOT_FOUND);
        }

        // Créer un nouvel objet Tutelles
        $tutelle = new Tutelles();

        // Attribuer les données à l'objet Tutelles
        $tutelle->setUai($data['uai']);
        $tutelle->setSiret($data['siret']);
        $tutelle->setType($data['type']);
        $tutelle->setInvestor($investor);
        $tutelle->setResearchCenter($researchCenter);

        // Ajouter le nouvel objet à la base de données
        $entityManager->persist($tutelle);
        $entityManager->flush();

        // Retourner la réponse JSON avec les détails du nouvel objet créé
        $tutelleArray = [
            'id' => $tutelle->getId(),
            'uai' => $tutelle->getUai(),
            'siret' => $tutelle->getSiret(),
            'type' => $tutelle->getType(),
            'investor_id' => $investor->getId(),
            'research_center_id' => $researchCenter->getId(),
        ];

        $tutelleJson = json_encode($tutelleArray);

        return new JsonResponse($tutelleJson, Response::HTTP_CREATED, [], true);
    }


}