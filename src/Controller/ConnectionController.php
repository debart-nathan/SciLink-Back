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
use App\Security\Voter\ContactVoter;

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
    public function register(Request $request, TokenStorageInterface $tokenStorage, SessionInterface $session, UsersRepository $usersRepository): Response
    {
        $data = json_decode($request->getContent(), true);



        // regarde si l'utilisateur existe déjà
        $existingUser = $usersRepository->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'User already exists'], Response::HTTP_CONFLICT);
        }

        // Valide email
        if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Email invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Valide mots de pass
        if (!isset($data['password']) || strlen($data['password']) < 8) {
            return new JsonResponse(
                ['error' => 'Mots de passe a besoin de au moins 8 char'],
                Response::HTTP_BAD_REQUEST
            );
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$,_\-.;:!%*?&])[A-Za-z\d@$,_\-.;:!%*?&]{8,}$/', $data['password'])) {
            // L'expression régulière vérifie les conditions suivantes :
            // ^(?=.*[a-z]) - Le mot de passe doit contenir au moins une lettre minuscule
            // (?=.*[A-Z]) - Le mot de passe doit contenir au moins une lettre majuscule
            // (?=.*\d) - Le mot de passe doit contenir au moins un chiffre
            // (?=.*[@$,_\-.;:!%*?&]) - Le mot de passe doit contenir au moins un caractère spécial
            // [A-Za-z\d@$!%*?&]{8,}$ - Le mot de passe doit contenir au moins 8 caractères
            return new JsonResponse(
                ['error' => 'Le mot de passe doit contenir au moins 8 caractères, 1 lettre majuscule,' .
                    '1 lettre minuscule, 1 chiffre et 1 caractère spécial'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Validate username
        if (!isset($data['user_name']) || strlen($data['user_name']) < 3) {
            return new JsonResponse(
                ['error' => "le nom d'utilisateur doit être au moins 3 char"],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (
            preg_match(

                "/[<>\/{};\/\.\\\?:\=\+\*\(\)$%\^&@#!\[\]|]/",
                $data['first_name']
            ) ||
            preg_match(
                "/[<>\/{};\/\.\\\?:\=\+\*\(\)$%\^&@#!\[\]|]/",

                $data['last_name']
            )
        ) {
            return new JsonResponse(
                ['error' => 'Le prénom et le nom ne doivent pas contenir de caractères spéciaux'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!isset($data['user_name']) || strlen($data['user_name']) > 25) {
            return new JsonResponse(
                ['error' => "le nom d'utilisateur doit être maximum 25 char"],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Valide first_name and last_name
        if (!isset($data['first_name']) || !isset($data['last_name'])) {
            return new JsonResponse(['error' => 'Nom et prénom requis'], Response::HTTP_BAD_REQUEST);
        }
        if (strlen($data['first_name']) > 25) {
            return new JsonResponse(['error' => "le prénom doit être maximum 25 char"], Response::HTTP_BAD_REQUEST);
        }


        if (strlen($data['last_name']) > 50) {
            return new JsonResponse(
                ['error' => "le nom de famille doit être maximum 50 char"],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = new Users();
        $user->setEmail($data['email']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $user->setUserName($data['user_name']);
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage->setToken($token);
        $session->set('_security_main', serialize($token));

        return new Response('User registered and logged in successfully', Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(
        Request $request,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        UsersRepository $usersRepository
    ): Response {
        $data = json_decode($request->getContent(), true);

        $email = $data['username'];
        $password = $data['password'];

        $user = $usersRepository->findOneBy(['email' => $email]);

        // vérifie que le mots de passe est valid
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

    #[Route('/is-logged-in', name: 'app_is_logged_in', methods: ['GET'])]
    public function isLoggedIn(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            return new JsonResponse(['error' => 'Not logged in'], Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse(['status' => 'Logged in'], Response::HTTP_OK);
    }

    #[Route('/is-admin', name: 'app_is_admin', methods: ['GET'])]
    public function isAdmin(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            return new JsonResponse(['error' => 'Not logged in'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var Users $loggedInUser */
        $loggedInUser = $token->getUser();

        if (!in_array('ROLE_ADMIN', $loggedInUser->getRoles())) {
            return new JsonResponse(['error' => 'Not an admin'], Response::HTTP_FORBIDDEN);
        }
        return new JsonResponse(['status' => 'Is an admin'], Response::HTTP_OK);
    }

    #[Route('/is-friend/{id}', name: 'app_is_friend', methods: ['GET'])]
    public function isFriend(
        ContactVoter $contactVoter,
        TokenStorageInterface $tokenStorage,
        UsersRepository $usersRepository,
        int $id
    ): JsonResponse {
        $user = $usersRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $token = $tokenStorage->getToken();
        if (!$token) {
            return new JsonResponse(['error' => 'Not logged in'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$contactVoter->voteOnAttribute('HAS_ACCEPTED_CONTACT', $user, $token)) {
            return new JsonResponse(['error' => 'Not friends'], Response::HTTP_FORBIDDEN);
        }
        return new JsonResponse(['status' => 'Is friends'], Response::HTTP_OK);
    }


    #[Route('/is-connected-users', name: 'app_is_connected_users', methods: ['POST'])]
    public function isConnectedUsers(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userIds = $data['user_ids'];

        $token = $tokenStorage->getToken();
        if (!$token) {
            return new JsonResponse(['error' => 'Not logged in'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var Users $loggedInUser */
        $loggedInUser = $token->getUser();
        $loggedInUserId = $loggedInUser->getId();

        foreach ($userIds as $userId) {
            if ($loggedInUserId === $userId) {
                return new JsonResponse(['status' => 'ok'], Response::HTTP_OK);
            }
        }

        return new JsonResponse(['error' => 'Not the connected user'], Response::HTTP_FORBIDDEN);
    }
    
    #[Route('/is-connected-user/{id}', name: 'app_is_connected_user', methods: ['GET'])]
    public function isConnectedUser(TokenStorageInterface $tokenStorage, int $id): JsonResponse
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            return new JsonResponse(['error' => 'Not logged in'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var Users $loggedInUser */
        $loggedInUser = $token->getUser();

        if ($loggedInUser->getId() !== $id) {
            return new JsonResponse(['error' => 'Not the connected user'], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse(['status' => 'Is the connected user'], Response::HTTP_OK);
    }

    #[Route('/Users/connected', name: 'app_connected_user_id', methods: ['GET'])]
    public function connectedUserId(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            return new JsonResponse(['error' => 'Not logged in'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var Users $loggedInUser */
        $loggedInUser = $token->getUser();

        return new JsonResponse(['connected_user_id' => $loggedInUser->getId()], Response::HTTP_OK);
    }
}
