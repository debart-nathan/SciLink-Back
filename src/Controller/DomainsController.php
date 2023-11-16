<?php

namespace App\Controller;

use App\Entity\Domains;
use App\Repository\DomainsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DomainsController extends AbstractController
{
    #[Route('/Domains', name: 'app_domains', methods: ['GET'])]
    public function index(DomainsRepository $domainsRepository, Request $request): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $domains = $domainsRepository->findBy($queryParams);
        } else {
            $domains = $domainsRepository->findAll();
        }
        $domainsArray = [];
        foreach ($domains as $domain) {
            $domainsArray[] = [
                'id' => $domain->getId(),
                'name' => $domain->getName(),
            ];
        }
        $domainsJson = json_encode($domainsArray);
        return new JsonResponse($domainsJson, 200, [], true);
    }

    #[Route('/Domains/{id}', name: 'app_domains_show', methods: ['GET'])]
    public function show(DomainsRepository $domainsRepository, Domains $domain): JsonResponse
    {
        $domainsArray = [
            'id' => $domain->getId(),
            'name' => $domain->getName(),
        ];
        $domainsJson = json_encode($domainsArray);
        return new JsonResponse($domainsJson, 200, [], true);
    }
}
