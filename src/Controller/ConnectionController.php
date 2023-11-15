<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UsersRepository;

class ConnectionController extends AbstractController
{
    private $passwordHasher;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = new Users();
        $user->setEmail($data['email']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $user->setUserName($data['username']);
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new Response('User registered successfully', Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, TokenStorageInterface $tokenStorage, SessionInterface $session, UsersRepository $usersRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['username'];
        $password = $data['password'];

        $user = $usersRepository->findOneBy(['email' => $email]);

        $isPasswordValid = $user ? $this->passwordHasher->isPasswordValid($user, $password) : false;

        if (!$user || !$isPasswordValid) {
            return new JsonResponse([
                'error' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // connecte l'utilisateur manuellement
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage->setToken($token);
        $session->set('_security_main', serialize($token));

        return new JsonResponse(['status' => 'ok'], Response::HTTP_OK);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout()
    {
        // cette route doit exister mais elle est géré automatiquement
    }

}
