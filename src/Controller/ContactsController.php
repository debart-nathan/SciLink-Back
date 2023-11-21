<?php

namespace App\Controller;

use App\Entity\Contacts;
use App\Entity\Users;
use App\Service\ResponseError;
use App\Repository\ContactsRepository;
use App\Repository\RelationStatusRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContactsController extends AbstractController
{
    #[Route('/Contacts', name: 'app_contacts', methods: ['GET'])]
    public function index(ContactsRepository $contactsRepository, Request $request): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            $contacts = $contactsRepository->findBy($queryParams);
        } else {
            $contacts = $contactsRepository->findAll();
        }
        $contactsArray = [];
        foreach ($contacts as $contact) {
            $contactsArray[] = [
                'id' => $contact->getId(),
                'send_date' => $contact->getSendDate()->format('d-m-y//H:i:s'),
                'object' => $contact->getObject(),
                'app_user_send_id' => $contact->getAppUserSend()->getId(),
                'app_user_receive_id' => $contact->getAppUserReceive()->getId(),
                'relation_status' => $contact->getRelationStatus()->getId(),
            ];
        }
        $contactsJson = json_encode($contactsArray);
        return new JsonResponse($contactsJson, 200, [], true);
    }

    #[Route('/Contacts/{id}', name: 'app_contacts_show', methods: ['GET'])]
    public function show(ContactsRepository $contactRepository, Contacts $contact): JsonResponse
    {
        $contactArray = [
            'id' => $contact->getId(),
            'send_date' => $contact->getSendDate()->format('d-m-y H:i:s'),
            'object' => $contact->getObject(),
            'app_user_send_id' => $contact->getAppUserSend()->getId(),
            'app_user_receive' => $contact->getAppUserReceive()->getId(),
            'relation_status' => $contact->getRelationStatus()->getId(),
        ];
        $contactJson = json_encode($contactArray);
        return new JsonResponse($contactJson, 200, [], true);
    }


    #[Route('/Contacts/{id}/patch', name: 'app_contacts_update', methods: ['PATCH'])]
    public function update(
        ContactsRepository $contactRepository,
        Contacts $contact,
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError,
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        if (!$token) {
            return new JsonResponse($responseError);
        }
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if ($loginUser->getId() !== $contact->getAppUserSend()->getId()) {
            return new JsonResponse($responseError);
        }
        $data = json_decode($request->getContent(), true);

        if (isset($data['object'])) {
            $contact->setObject($data['object']);
        }
        if ($loginUser->getId() !== $contact->getId()) {
            return new JsonResponse($responseError);
        }
        $entityManager->persist($contact);
        $entityManager->flush();

        $contactArray = [
            'id' => $contact->getId(),
            'send_date' => $contact->getSendDate()->format('d-m-y H:i:s'),
            'object' => $contact->getObject(),
            'app_user_send_id' => $contact->getAppUserSend()->getId(),
            'app_user_receive_id' => $contact->getAppUserReceive()->getId(),
            'relation_status' => $contact->getRelationStatus()->getId(),
        ];
        $contactJson = json_encode($contactArray);
        return new JsonResponse($contactJson, 200, [], true);
    }

    #[Route('/Contacts/{id}/response', name: 'app_contacts_response', methods: ['PATCH'])]
    public function respondToContact(
        Contacts $contact,
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        RelationStatusRepository $relationStatusRepository,
        ResponseError $responseError
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!$token) {
            return new JsonResponse($responseError);
        }
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // Check if the logged in user is the recipient of the contact
        if ($loginUser->getId() !== $contact->getAppUserReceive()->getId()) {

            return new JsonResponse($responseError);
        }
        $data = json_decode($request->getContent(), true);
        // Check if the response is set and is either 'accepted' or 'refused'
        if (isset($data['response']) && in_array($data['response'], ['accepted', 'refused'])) {
            $contact->setRelationStatus($relationStatusRepository->findOneBy(['name' => $data['response']]));
            $entityManager->persist($contact);
            $entityManager->flush();
        } else {
            return new JsonResponse(['error' => 'Invalid response.'], Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse(['message' => 'Contact response updated successfully.'], Response::HTTP_OK);
    }

    #[Route('/Contacts/create/post', name: 'app_contacts_create', methods: ['POST'])]
    public function create(
        Request $request,
        Contacts $contact,
        RelationStatusRepository $relationStatusRepository,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        EmailService $emailService,
        ResponseError $responseError,
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!$token) {
            return new JsonResponse($responseError);
        }
        $data = json_decode($request->getContent(), true);

        $contact = new Contacts();
        $contact->setObject($data['object']);
        $contact->setSendDate(new \DateTime());
        $contact->setAppUserSend($token->getUser());
        $contact->setAppUserReceive($data['app_user_receive_id']);
        $contact->setRelationStatus($relationStatusRepository->findOneBy(['name' => 'pending']));


        $entityManager->persist($contact);
        $entityManager->flush();

        $emailService->sendRelationRequest($token->getUser(), $data['app_user_receive_id']);

        $contactArray = [
            'id' => $contact->getId(),
            'send_date' => $contact->getSendDate()->format('d-m-y H:i:s'),
            'object' => $contact->getObject(),
            'app_user_send_id' => $contact->getAppUserSend()->getId(),
            'app_user_receive_id' => $contact->getAppUserReceive()->getId(),
            'relation_status' => $contact->getRelationStatus()->getId()
        ];
        $contactJson = json_encode($contactArray);
        return new JsonResponse($contactJson, 200, [], true);
    }



    #[Route('/Contacts/{id}/delete', name: 'delete_contact', methods: ['DELETE'])]
    public function deleteContact(int $id, EntityManagerInterface $entityManager, ContactsRepository $contactsRepository, Contacts $contact): JsonResponse
    {

        $contact = $contactsRepository->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Contact not found');
        }
        $entityManager->remove($contact);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Contact deleted'], 200);
    }

}
