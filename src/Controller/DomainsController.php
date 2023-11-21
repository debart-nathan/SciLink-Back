<?php

namespace App\Controller;

use App\Entity\Domains;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DomainsRepository;
use App\Repository\LocationsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function show(
        DomainsRepository $domainsRepository,
        Domains $domain
    ): JsonResponse {
        $domainsArray = [
            'id' => $domain->getId(),
            'name' => $domain->getName(),
        ];
        $domainsJson = json_encode($domainsArray);
        return new JsonResponse($domainsJson, 200, [], true);
    }

    #[Route('/Domains/create/post', name: 'app_domains_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate that the required data is present
        if (!isset($data['name'])) {
            return new JsonResponse(['error' => 'Name is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Create a new Domains entity
        $domain = new Domains();
        $domain->setName($data['name']);

        // Persist the new entity to the database
        $entityManager->persist($domain);
        $entityManager->flush();

        // Return the newly created domain in the response
        $domainArray = [
            'id' => $domain->getId(),
            'name' => $domain->getName(),
        ];
        $domainJson = json_encode($domainArray);

        return new JsonResponse($domainJson, JsonResponse::HTTP_CREATED, [], true);
    }

    
    #[Route('/Domains/{id}/delete', name: 'delete_domain', methods: ['DELETE'])]
    public function deleteDomain(int $id, EntityManagerInterface $entityManager, DomainsRepository $domainsRepository, Domains $domain): JsonResponse
    {

        $domain = $domainsRepository->find($id);

        if (!$domain) {
            throw $this->createNotFoundException('Domain not found');
        }
        $entityManager->remove($domain);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Domain deleted'], 200);

    }
}
