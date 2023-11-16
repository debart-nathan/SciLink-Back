<?php

namespace App\Controller;


use App\Repository\LocationsRepository;
use App\Repository\ResearchCentersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LocatedsController extends AbstractController
{
    #[Route('/Locateds', name: 'app_locateds')]
    public function index(LocationsRepository $locationsRepository,ResearchCentersRepository $researchCentersRepository, Request $request,): JsonResponse
    {
        // Vérifie s'il y a des paramètres de requête
        if ($request->query->count() > 0) {
            $queryParams = $request->query->all();
            // Vérifie si l'identifiant de la locations est présent dans les paramètres
            if (isset($queryParams['location_id'])) {
                $locationId = $queryParams['location_id'];
            }
            // Vérifie si l'identifiant du domaine est présent dans les paramètres
            if (isset($queryParams['research_center_id'])) {
                $researchCenterId  = $queryParams['research_center_id'];
            }
        }
        $locatedsArray = [];
        // Si l'identifiant du location est spécifié
        if (isset($locationId)) {
            $location = $locationsRepository->find($locationId);
            $researchCenters = $location->getResearchCenters();
            foreach ($researchCenters as $researchCenter) {
                // Construit le tableau des locations pour le located
                $locatedsArray[] = [
                    'location_id' => $locationId,
                    'research_center_id' => $researchCenter->getId(),
                ];
            }
        }
        // Si l'identifiant du locations est spécifié
        elseif (isset($researchCenterId)) {
            $researchCenter = $researchCentersRepository->find($researchCenterId);
            $locations = $researchCenter->getLocated();
            // Construit le tableau des pratiques pour le domaine et ses chercheurs
            foreach ($locations as $location) {
                $locatedsArray[] = [
                    'location_id' =>  $location->getId(),
                    'research_center_id' => $researchCenterId,
                ];
            }
        }
        // Si aucun identifiant spécifié, récupère toutes les location
        else {
            $locations = $locationsRepository->findAll();
            // Construit le tableau des pratiques pour tous les chercheurs et leurs locations
            foreach ($locations as $location) {
                $researchCenters = $location->getResearchCenters();
                foreach ($researchCenters as $researchCenter ) {
                    $locatedsArray[] = [
                        'location_id' => $location->getId(),
                        'research_center_id' => $researchCenter->getId(),

                    ];
                }
            }
        }
        // Convertit le tableau en JSON et retourne une réponse JSON
        $locatedsJson = json_encode($locatedsArray);
        return new JsonResponse($locatedsJson , 200, [], true);
    }
}
