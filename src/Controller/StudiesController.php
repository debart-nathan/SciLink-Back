<?php

namespace App\Controller;

use App\Repository\DomainsRepository;
use App\Repository\ResearchCentersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StudiesController extends AbstractController
{
    #[Route('/Studies', name: 'app_studies')]
    public function index(Request $request, ResearchCentersRepository $researchCentersRepository, DomainsRepository $domainsRepository): JsonResponse
    {
        // Vérifie s'il y a des paramètres de requête
        if ($request->query->count() > 0) {
            $queryParams = $request->query->all();
            // Vérifie si l'identifiant du centre de recherche est présent dans les paramètres
            if (isset($queryParams['research_center_id'])) {
                $researchCenterId = $queryParams['research_center_id'];
            }
            // Vérifie si l'identifiant du domaine est présent dans les paramètres
            if (isset($queryParams['domain_id'])) {
                $domainId = $queryParams['domain_id'];
            }
        }
        $studiesArray = [];
        // Si l'identifiant du centre de recherche est spécifié
        if (isset($researchCenterId)) {
            $researchCenter = $researchCentersRepository->find($researchCenterId);
            $domains = $researchCenter->getDomains();
            foreach ($domains as $domain) {
                // Construit le tableau des studies pour le centre de recherche et ses domaines
                $studiesArray[] = [
                    'research_center_id' => $researchCenterId,
                    'domain_id' => $domain->getId(),
                ];
            }
        }
        // Si l'identifiant du domaine est spécifié
        elseif (isset($domainId)) {
            $domain = $domainsRepository->find($domainId);
            $researchCenters = $domain->getResearchCenters();
            // Construit le tableau des studies pour le domaine et ses centres de recherche
            foreach ($researchCenters as $researchCenter) {
                $studiesArray[] = [
                    'research_center_id' => $researchCenter->getId(),
                    'domain_id' => $domainId,
                ];
            }
        }
        // Si aucun identifiant spécifié, récupère toutes les studies
        else {
            $researchCenters = $researchCentersRepository->findAll();
            // Construit le tableau des studies pour tous les centres de recherche et leurs domaines
            foreach ($researchCenters as $researchCenter) {
                $domains = $researchCenter->getDomains();
                foreach ($domains as $domain) {
                    $studiesArray[] = [
                        'researcher_id' => $researchCenter->getId(),
                        'domain_id' => $domain->getId(),
                    ];
                }
            }
        }
        // Convertit le tableau en JSON et retourne une réponse JSON
        $studiesJson = json_encode($studiesArray);
        return new JsonResponse($studiesJson, 200, [], true);
    }
}