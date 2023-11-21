<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Locations;
use App\Service\ResponseError;
use App\Repository\LocationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LocationsController extends AbstractController
{
    #[Route('/Locations', name: 'app_locations', methods: ['GET'])]
    public function index(
        LocationsRepository $locationsRepository,
        Request $request
    ): JsonResponse {
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
                'postal_code'  => $location->getPostalCode(),
                'commune' => $location->getCommune(),
            ];
        }
        $locationsJson = json_encode($locationsArray);
        return new JsonResponse($locationsJson, 200, [], true);
    }

    #[Route('/Locations/{id}', name: 'app_locations_show', methods: ['GET'])]
    public function show(Locations $locations): JsonResponse
    {
        $locationsArray = [
            'id' =>       $locations->getId(),
            'address' =>  $locations->getAddress(),
            'postal_code'  => $locations->getPostalCode(),
            'commune' => $locations->getCommune(),
        ];
        $locationsJson = json_encode($locationsArray);
        return new JsonResponse($locationsJson, 200, [], true);
    }



    #[Route('/Locations/{id}/patch', name: 'app_locations_update', methods: ['PATCH'])]
    public function update(
        Locations $locations,
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
        if (!($token && ($loginUser->getId() === $locations->getId()))) {
            return new JsonResponse($responseError);
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
            'postal_code'  => $locations->getPostalCode(),
            'commune' => $locations->getCommune(),


        ];
        $locationsJson = json_encode($locationsArray);
        return new JsonResponse($locationsJson, 200, [], true);
    }

    #[Route('/Locations/{id}/delete', name: 'app_locations_delete', methods: ['DELETE'])]
    public function deleteLocations(
        int  $id,
        Request $request,
        Locations $location,
        EntityManagerInterface $entityManager,
        LocationsRepository $locationsRepository
         ): JsonResponse
    {
        $location = $locationsRepository->find($id);
 
        if ($request->isMethod('DELETE')) {
           
            $entityManager->remove($location);
            $entityManager->flush();

            return new JsonResponse(['status' => 'Location deleted successfully'], 200);
        }

        return new JsonResponse(['status' => 'Method not allowed'], 405);
    }
    #[Route('/locations/create/post', name: 'app_locations_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError
    ): JsonResponse
    {
        //récupérer le jeton d'authentification de l'utilisateur connecté
        $token = $tokenStorage->getToken();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!$token) {
            return new JsonResponse($responseError);//code HTTP 401
        }
      
        $data = json_decode($request->getContent(), true);

        $location = new Locations();
        $location->setAddress($data['address']);
        $location->setPostalCode($data['postal_code']);
        $location->setCommune($data['commune']);


        $entityManager->persist($location);
        $entityManager->flush();

        $locationsArray = [
            'id' => $location->getId(),
            'address' => $location->getAddress(),
            'postal_code' => $location->getPostalCode(),
            'commune' => $location->getCommune(),
        ];
        $locationsJson = json_encode($locationsArray);
        return new JsonResponse($locationsJson, Response::HTTP_CREATED, [], true);

    }
}