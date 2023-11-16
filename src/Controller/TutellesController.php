<?php

namespace App\Controller;

use App\Entity\Tutelles;
use App\Entity\Users;
use App\Repository\TutellesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TutellesController extends AbstractController
{
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
        TokenStorageInterface $tokenStorage
        ): JsonResponse

    {
        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!($token && ($loginUser->getId() === $tutelle->getInvestor()->getId()))) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_UNAUTHORIZED);
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
}