<?php

namespace App\Controller;

use App\Entity\Tutelles;
use App\Repository\TutellesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TutellesController extends AbstractController
{
    #[Route('/Tutelles', name: 'app_tutelles', methods: ['GET'])]
    public function index(TutellesRepository $tutellesRepository, Request $request): JsonResponse
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
                'investor' => $tutelle->getInvestor()->getId(),
                'researchCenter' => $tutelle->getResearchCenter()->getId(),
            ];
        }
        $tutellesJson = json_encode($tutellesArray);
        return new JsonResponse($tutellesJson, 200, [], true);
    }

    #[Route('/Tutelles/{id}', name: 'app_tutelles_show', methods: ['GET'])]
    public function show(TutellesRepository $tutelleRepository, Tutelles $tutelle): JsonResponse
    {

        $tutelleArray = [
            'id' => $tutelle->getId(),
            'uai' => $tutelle->getUai(),
            'siret' => $tutelle->getSiret(),
            'type' => $tutelle->getType(),
            'investor' => $tutelle->getInvestor()->getId(),
            'researchCenter' => $tutelle->getResearchCenter()->getId(),

        ];
        $tutelleJson = json_encode($tutelleArray);
        return new JsonResponse($tutelleJson, 200, [], true);
    }

    #[Route('/Tutelles/{id}', name: 'app_tutelles_update', methods: ['PATCH'])]
    public function update(TutellesRepository $tutelleRepository, Tutelles $tutelle, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
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
        if (isset($data['investor'])) {
            $tutelle->setInvestor($data['investor']);
        }
        if (isset($data['researchCenter'])) {
            $tutelle->setResearchCenter($data['researchCenter']);
        }

        $entityManager->persist($tutelle);
        $entityManager->flush();


        $tutelleArray = [
            'id' => $tutelle->getId(),
            'uai' => $tutelle->getUai(),
            'siret' => $tutelle->getSiret(),
            'type' => $tutelle->getType(),
            'investor' => $tutelle->getInvestor()->getId(),
            'researchCenter' => $tutelle->getResearchCenter()->getId(),

        ];
        $tutelleJson = json_encode($tutelleArray);
        return new JsonResponse($tutelleJson, 200, [], true);
    }
}