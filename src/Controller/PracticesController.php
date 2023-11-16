<?php

namespace App\Controller;

use App\Repository\DomainsRepository;
use App\Repository\ResearchersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PracticesController extends AbstractController
{
    #[Route('/Practices', name: 'app_practices')]
    public function index(
        Request $request,
        ResearchersRepository $researchersRepository,
        DomainsRepository $domainsRepository
        ): JsonResponse
    {
        // Vérifie s'il y a des paramètres de requête
        if ($request->query->count() > 0) {
            $queryParams = $request->query->all();
            // Vérifie si l'identifiant du rechercheur est présent dans les paramètres
            if (isset($queryParams['researcher_id'])) {
                $researcherId = $queryParams['researcher_id'];
            }
            // Vérifie si l'identifiant du domaine est présent dans les paramètres
            if (isset($queryParams['domain_id'])) {
                $domainId = $queryParams['domain_id'];
            }
        }
        $practicesArray = [];
        // Si l'identifiant du chercheur est spécifié
        if (isset($researcherId)) {
            $researcher = $researchersRepository->find($researcherId);
            $domains = $researcher->getDomains();
            foreach ($domains as $domain) {
                // Construit le tableau des pratiques pour le chercheur et ses domaines
                $practicesArray[] = [
                    'researcher_id' => $researcherId,
                    'domain_id' => $domain->getId(),
                ];
            }
        }
        // Si l'identifiant du domaine est spécifié
        elseif (isset($domainId)) {
            $domain = $domainsRepository->find($domainId);
            $researchers = $domain->getResearchers();
            // Construit le tableau des pratiques pour le domaine et ses chercheurs
            foreach ($researchers as $researcher) {
                $practicesArray[] = [
                    'researcher_id' => $researcher->getId(),
                    'domain_id' => $domainId,
                ];
            }
        }
        // Si aucun identifiant spécifié, récupère toutes les pratiques
        else {
            $researchers = $researchersRepository->findAll();
            // Construit le tableau des pratiques pour tous les chercheurs et leurs domaines
            foreach ($researchers as $researcher) {
                $domains = $researcher->getDomains();
                foreach ($domains as $domain) {
                    $practicesArray[] = [
                        'researcher_id' => $researcher->getId(),
                        'domain_id' => $domain->getId(),
                    ];
                }
            }
        }
        // Convertit le tableau en JSON et retourne une réponse JSON
        $practicesJson = json_encode($practicesArray);
        return new JsonResponse($practicesJson, 200, [], true);
    }
}