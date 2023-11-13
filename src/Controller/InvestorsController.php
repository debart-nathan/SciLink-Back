<?php

namespace App\Controller;

use App\Entity\Investors;
use App\Repository\InvestorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InvestorsController extends AbstractController
{
    #[Route('/Investors', name: 'app_investors', methods: ['GET'])]
    public function index(InvestorsRepository $investorsRepository, Request $request): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $investors = $investorsRepository->findBy($queryParams);
        } else {
            $investors = $investorsRepository->findAll();
        }
        $investorsArray = [];
        foreach ($investors as $investor) {
            $user_id = $investor->getAppUser() ? $investor->getAppUser()->getId() : null;
            $investorsArray[] = [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'sigle' => $investor->getSigle(),
                'type' => $investor->getType(),
                'label' => $investor->getLabel(),
                'user_id' => $user_id,
            ];
        }
        $investorsJson = json_encode($investorsArray);
        return new JsonResponse($investorsJson, 200, [], true);
    }

    #[Route('/Investors/{id}', name: 'app_investors_show', methods: ['GET'])]
    public function show(InvestorsRepository $investorRepository, Investors $investor): JsonResponse
    {
        $user_id = $investor->getAppUser() ? $investor->getAppUser()->getId() : null;
        $investorArray = [
            'id' => $investor->getId(),
            'name' => $investor->getName(),
            'sigle' => $investor->getSigle(),
            'type' => $investor->getType(),
            'label' => $investor->getLabel(),
            'user_id' => $user_id,
        ];
        $investorJson = json_encode($investorArray);
        return new JsonResponse($investorJson, 200, [], true);
    }

    #[Route('/Investors/{id}', name: 'app_investors_update', methods: ['PATCH'])]
    public function update(InvestorsRepository $investorRepository, Investors $investor, Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $investor->setName($data['name']);
        }
        if (isset($data['sigle'])) {
            $investor->setSigle($data['sigle']);
        }
        if (isset($data['type'])) {
            $investor->setType($data['type']);
        }
        if (isset($data['label'])) {
            $investor->setLabel($data['label']);
        }

        $entityManager->persist($investor);
        $entityManager->flush();

        $user_id = $investor->getAppUser() ? $investor->getAppUser()->getId() : null;
        $investorArray = [
            'id' => $investor->getId(),
            'name' => $investor->getName(),
            'sigle' => $investor->getSigle(),
            'type' => $investor->getType(),
            'label' => $investor->getLabel(),
            'user_id' => $user_id,
        ];
        $investorJson = json_encode($investorArray);
        return new JsonResponse($investorJson, 200, [], true);
    }
}
