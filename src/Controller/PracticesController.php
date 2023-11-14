<?php

namespace App\Controller;

use App\Repository\DomainsRepository;
use App\Repository\ResearchersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PracticesController extends AbstractController
{
    #[Route('/Practices', name: 'app_practices')]
    public function index(Request $request, ResearchersRepository $researchersRepository, DomainsRepository $domainsRepository): JsonResponse
    {
        if ($request->query->count() > 0) {
            $queryParams = $request->query->all();
            if (isset($queryParams['researcher_id'])) {
                $researcherId = $queryParams['researcher_id'];
            }
            if (isset($queryParams[''])) {
                $domainId = $queryParams['domain_id'];
            }
        }
        $practicesArray = [];
        if (isset($researcherId) && isset($domainId)) {
            $researcher = $researchersRepository->find($researcherId);
        }
        if (isset($researcherId)) {
            $researcher = $researchersRepository->find($researcherId);
            $domains = $researcher->getDomains();
            foreach ($domains as $domain) {
                $practicesArray[] = [
                    'researcher_id' => $researcherId,
                    'domain_id' => $domain->getId(),
                ];
            }
        } elseif (isset($domainId)) {
            $domain = $domainsRepository->find($domainId);
            $researchers = $domain->getResearchers();
            foreach ($researchers as $researcher) {
                $practicesArray[] = [
                    'researcher_id' => $researcher->getId(),
                    'domain_id' => $domainId,
                ];
            }
        } else {
            $researchers = $researchersRepository->findAll();
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
        $practicesJson = json_encode($practicesArray);
        return new JsonResponse($practicesJson, 200, [], true);
    }
}