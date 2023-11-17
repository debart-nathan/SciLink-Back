<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use App\Repository\ResearchCentersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RepresentedBysController extends AbstractController
{
    #[Route('/RepresentedBys', name: 'app_represented_bys')]
    public function index(
        Request $request,
        ResearchCentersRepository $researchCentersRepository,
        UsersRepository $usersRepository
        ): JsonResponse
    {
        // Vérifie s'il y a des paramètres de requête
        if ($request->query->count() > 0) {
            $queryParams = $request->query->all();
            // Vérifie si l'identifiant du centre de recherche est présent dans les paramètres
            if (isset($queryParams['research_center_id'])) {
                $researchCenterId = $queryParams['research_center_id'];
            }
            // Vérifie si l'identifiant du user est présent dans les paramètres
            if (isset($queryParams['user_id'])) {
                $userId = $queryParams['user_id'];
            }
        }
        $representedBysArray = [];
        // Si l'identifiant du centre de recherche est spécifié
        if (isset($researchCenterId)) {
            $researchCenter = $researchCentersRepository->find($researchCenterId);
            $users = $researchCenter->getUsers();
            foreach ($users as $user) {
                // Construit le tableau des representedBys pour le centre de recherche et ses useres
                $representedBysArray[] = [
                    'research_center_id' => $researchCenterId,
                    'user_id' => $user->getId(),
                ];
            }
        }
        // Si l'identifiant du user est spécifié
        elseif (isset($userId)) {
            $user = $usersRepository->find($userId);
            $researchCenters = $user->getResearchCenters();
            // Construit le tableau des representedBys pour le user et ses centres de recherche
            foreach ($researchCenters as $researchCenter) {
                $representedBysArray[] = [
                    'research_center_id' => $researchCenter->getId(),
                    'user_id' => $userId,
                ];
            }
        }
        // Si aucun identifiant spécifié, récupère toutes les representedBys
        else {
            $researchCenters = $researchCentersRepository->findAll();
            // Construit le tableau des representedBys pour tous les centres de recherche et leurs users
            foreach ($researchCenters as $researchCenter) {
                $users = $researchCenter->getUsers();
                foreach ($users as $user) {
                    $representedBysArray[] = [
                        'researcher_id' => $researchCenter->getId(),
                        'user_id' => $user->getId(),
                    ];
                }
            }
        }
        // Convertit le tableau en JSON et retourne une réponse JSON
        $representedBysJson = json_encode($representedBysArray);
        return new JsonResponse($representedBysJson, 200, [], true);
    }
}