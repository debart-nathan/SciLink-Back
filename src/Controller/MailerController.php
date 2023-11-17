<?php

namespace App\Controller;

use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MailerController extends AbstractController
{
    private $emailService;
    private $tokenStorage;
    private $params;

    public function __construct(EmailService $emailService, TokenStorageInterface $tokenStorage, ParameterBagInterface $params)
    {
        $this->emailService = $emailService;
        $this->tokenStorage = $tokenStorage;
        $this->params = $params;
    }

    #[Route('/send-contact-ticket', name: 'send_contact_ticket', methods: ['POST'])]
    public function sendContactTicket(Request $request): Response
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return new Response('No token found.', Response::HTTP_UNAUTHORIZED);
        }

        /** @var Users $user */
        $user = $token->getUser();
        if (!$user) {
            return new Response('No user is currently logged in.', Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        $sender = $user->getEmail();
        $recipient = $this->params->get('company_email');
        $subject = 'Contact Ticket from ' . $user->getUsername();

        $this->emailService->sendEmail($sender, $recipient, $subject, $message);

        return new Response('Contact ticket sent!');
    }

    #[Route('/send-link-request', name: 'send_link_request', methods: ['POST'])]
    public function sendLinkRequest(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return new Response('Aucun jeton trouvé.', Response::HTTP_UNAUTHORIZED);
        }

        /** @var Users $user */
        $user = $token->getUser();
        if (!$user) {
            return new Response('Aucun utilisateur n\'est actuellement connecté.', Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $profileType = $data['profileType'] ?? '';
        $profileId = $data['profileId'] ?? '';

        // Check if the profile exists in your database
        $repository = $entityManager->getRepository('App\\Entity\\' . $profileType);
        $profile = $repository->find($profileId);
        if (!$profile) {
            return new Response('Demande invalide.', Response::HTTP_BAD_REQUEST);
        }

        $sender = $user->getEmail();
        $recipient = $this->params->get('company_email');
        $subject = 'Demande de lien de ' . $user->getUsername();
        $message = 'L\'utilisateur ' . $user->getUsername() . ' souhaite lier son compte au profil ' . $profileType . ' avec l\'id ' . $profileId;

        $this->emailService->sendEmail($sender, $recipient, $subject, $message);

        return new Response('Demande de lien envoyée!');
    }
}
