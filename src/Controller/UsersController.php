<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UsersRepository;
use App\Security\Voter\ContactVoter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UsersController extends AbstractController
{


    #[Route('/Users', name: 'app_users', methods: ['GET'])]
    public function index(
        ContactVoter $contactVoter,
        TokenStorageInterface $tokenStorage,
        Request $request,
        UsersRepository $usersRepository
    ): JsonResponse {
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $users = $usersRepository->findBy($queryParams);
        } else {
            $users = $usersRepository->findAll();
        }
        $token = $tokenStorage->getToken();
        $usersArray = [];
        $privacySecurity = false;
        $locationPrivacy = false;
        foreach ($users as $user) {
            if ($token) {
                /** @var Users $loginUser */
                $loginUser = $token->getUser();
                $privacySecurity = (
                    // vérifie que l'utilisateur connecté est l'utilisateur de la donné
                    (($loginUser->getId() === $user->getId())) ||
                    // vérifie que l'utilisateur connecté a une relation accepté avec l’utilisateur de la donné
                    $contactVoter->voteOnAttribute('HAS_ACCEPTED_CONTACT', $user, $token)
                    
                );

                $locationPrivacy =(($loginUser->getId() === $user->getId())&&$user->getLocation());
            }

            

            $usersArray[] = [
                'id' => $user->getId(),
                'user_name' => $user->getUserName(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $privacySecurity ? $user->getEmail() : null,
                'location_id' => $locationPrivacy ? $user->getLocation()->getId() : null,

            ];
        }
        $usersJson = json_encode($usersArray);
        return new JsonResponse($usersJson, 200, [], true);
    }

    #[Route('/Users/{id}', name: 'app_users_show', methods: ['GET'])]

    public function show(
        ContactVoter $contactVoter,
        TokenStorageInterface $tokenStorage,
        Users $user
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        $privacySecurity=false;
        if ($token) {
            /** @var Users $loginUser */
            $loginUser = $token->getUser();
            $privacySecurity = (
                // vérifie que l'utilisateur connecté est l'utilisateur de la donné
                (($loginUser->getId() === $user->getId())) ||
                // vérifie que l'utilisateur connecté a une relation accepté avec l’utilisateur de la donné
                $contactVoter->voteOnAttribute('HAS_ACCEPTED_CONTACT', $user, $token)

            );
        }
        $userArray = [
            'id' => $user->getId(),
            'user_name' => $user->getUserName(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $privacySecurity ? $user->getEmail() : null,
            'location_id' => $user->getLocation() ? $user->getLocation()->getId() : null,
        ];

        $userJson = json_encode($userArray);
        return new JsonResponse($userJson, 200, [], true);
    }


    #[Route('/Users/{id}/patch', name: 'app_users_update', methods: ['PATCH'])]
    public function update(
        TokenStorageInterface $tokenStorage,
        UsersRepository $usersRepository,
        Users $user,
        Request $request
    ): JsonResponse {
        $security = false
        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token ? $token->getUser() : null;
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!($token && ($loginUser->getId() === $user->getId()))) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['user_name'])) {
            $user->setUserName($data['user_name']);
        }

        if (isset($data['first_name'])) {
            $user->setFirstName($data['first_name']);
        }

        if (isset($data['last_name'])) {
            $user->setLastName($data['last_name']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        $usersRepository->persist($user);
        $usersRepository->flush();

        $userArray = [
            'id' => $user->getId(),
            'user_name' => $user->getUserName(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $user->getEmail(),
            'location_id' => $user->getLocation()->getId(),
        ];

        $userJson = json_encode($userArray);

        return new JsonResponse($userJson, 200, [], true);
    }

    #[Route('/Users/{id}/delete', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id, EntityManagerInterface $entityManager, UsersRepository $userRepository, Users $user,): JsonResponse
    {

        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'User deleted'], 200);
    }
}
