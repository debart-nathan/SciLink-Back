<?php

namespace App\Controller;

use App\Entity\Investors;
use App\Entity\Users;
use App\Service\ResponseError;
use App\Repository\InvestorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InvestorsController extends AbstractController
{
    #[Route('/Investors', name: 'app_investors', methods: ['GET'])]
    public function index(
        InvestorsRepository $investorsRepository,
        Request $request
        ): JsonResponse
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
    public function show(
        InvestorsRepository $investorRepository,
        Investors $investor
        ): JsonResponse
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


    #[Route('/Investors/{id}/patch', name: 'app_investors_update', methods: ['PATCH'])]
    public function update(
        InvestorsRepository $investorRepository,
        Investors $investor, Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError
        ): JsonResponse

    {
        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!($token && ($loginUser->getId() === $investor->getId()))) {
            return new JsonResponse($responseError);
        }
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

    #[Route('/investors/create/post', name: 'app_investors_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ResponseError $responseError,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!$token) {
            return new JsonResponse($responseError);
        }
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        
        $data = json_decode($request->getContent(), true);

        $investor = new Investors();
        $investor->setName($data['name']);
        $investor->setSigle($data['sigle']);
        $investor->setType($data['type']);
        $investor->setLabel($data['label']);

        $investor->setAppUser($loginUser);

        $entityManager->persist($investor);
        $entityManager->flush();

        $user_id = $investor->getAppUser() ? $investor->getAppUser()->getId() : null;
        $investorArray = [
            'id' => $investor->getId(),
            'name' => $investor->getName(),
            'sigle' => $investor->getSigle(),
            'type' => $investor->getType(),
            'label' => $investor->getLabel(),
            'app_user_id' => $user_id,
        ];
        $investorJson = json_encode($investorArray);

        return new JsonResponse($investorJson, Response::HTTP_CREATED, [], true);
    }

    #[Route('/investors/{id}/delete', name: 'app_investors_delete', methods: ['DELETE'])]
    public function delete(
        InvestorsRepository $investorRepository,
        Investors $investor,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError,
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token->getUser();

        // Vérifiez si l'utilisateur connecté a les autorisations nécessaires pour supprimer l'investisseur
        if (!($token && ($loginUser->getId() === $investor->getAppUser()->getId()))) {
            return new JsonResponse($responseError);
        }

        $entityManager->remove($investor);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
