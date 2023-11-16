<?php

namespace App\Controller;

use App\Entity\Locations;
use App\Repository\LocationsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;

class LocationsController extends AbstractController
{
    #[Route('/Locations', name: 'app_locations', methods: ['GET'])]
    public function index(LocationsRepository $locationsRepository, Request $request): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $locations = $locationsRepository->findBy($queryParams);
        } else {
            $locations  = $locationsRepository->findAll();
        }
        $locationsArray = [];
        foreach ($locations  as $location) {
            $locationsArray[] = [
                'id' =>       $location->getId(),
                'address' =>  $location->getAddress(),
                'postal_code'  => $location ->getPostalCode(),
                'commune' => $location->getCommune(),
            ];
        }
        $locationsJson = json_encode($locationsArray);
        return new JsonResponse($locationsJson, 200, [], true);
    }

    #[Route('/Locations/{id}', name: 'app_locations_show', methods: ['GET'])]
    public function show( Locations $locations): JsonResponse
    {
        $locationsArray = [
               'id' =>       $locations->getId(),
               'address' =>  $locations->getAddress(),
               'postal_code'  => $locations ->getPostalCode(),
               'commune' => $locations->getCommune(),
        ];
        $locationsJson = json_encode($locationsArray);
        return new JsonResponse($locationsJson, 200, [], true);
    }


    #[Route('/Locations/{id}', name: 'app_locations_update', methods: ['PATCH'])]
    public function update( Locations $locations, Request $request,EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!($token && ($token->getUser()->getId() === $user->getId()))) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_UNAUTHORIZED);
        }
  
        $data = json_decode($request->getContent(), true);

        if (isset($data['address'])) {
            $locations->setAddress($data['address']);
        }
        if (isset($data['postal_code'])) {
            $locations->setPostalCode($data['postal_code']);
        }
        if (isset($data['commune'])) {
            $locations->setCommune($data['commune']);
        }
      

        $entityManager->persist($locations);
        $entityManager->flush();

 
        $locationsArray = [
            'id' =>       $locations->getId(),
            'address' =>  $locations->getAddress(),
            'postal_code'  => $locations ->getPostalCode(),
            'commune' => $locations->getCommune(),
         
            
        ];
        $locationsJson = json_encode($locationsArray );
        return new JsonResponse($locationsJson, 200, [], true);
    }
}