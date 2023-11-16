<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UsersController extends AbstractController
{
    #[Route('/Users', name: 'app_users', methods: ['GET'])]
    public function index(Request $request, UsersRepository $usersRepository): JsonResponse
    {
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $users = $usersRepository->findBy($queryParams);
        } else {
            $users = $usersRepository->findAll();
        }
        $usersArray = [];
        foreach ($users as $user) {
            $usersArray[] = [
                'id' => $user->getId(),
                'user_name' => $user->getUserName(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
                // 'location_id' => $user->getLocation()->getId(),
            ];
        }
        $usersJson = json_encode($usersArray);
        return new JsonResponse($usersJson, 200, [], true);
    }

    #[Route('/Users/{id}', name: '', methods: ['GET'])]
    public function show(UsersRepository $usersRepository, Users $user): JsonResponse
    {
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

    #[Route('/Users/{id}', name: '', methods: ['PATCH'])]
    public function update(UsersRepository $usersRepository, Users $user, Request $request): JsonResponse
    {
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
