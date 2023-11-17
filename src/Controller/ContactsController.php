<?php

namespace App\Controller;

use App\Entity\Contacts;
use App\Entity\Users;
use App\Service\ResponseError;
use App\Repository\ContactsRepository;
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
        Contacts $contact, Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError,
        ): JsonResponse
    {
        $token = $tokenStorage->getToken();
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!($token && ($loginUser->getId() === $contact->getAppUserSend()->getId()))) {

            return new JsonResponse($responseError);
        }
        $data = json_decode($request->getContent(), true);

        if (isset($data['object'])) {
            $contact->setObject($data['object']);
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
    #[Route('/Contacts/post', name: 'app_contacts_create', methods: ['POST'])]
    public function create(
        Request $request,
        Contacts $contact,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ResponseError $responseError,
        ): JsonResponse
    {
        $token = $tokenStorage->getToken();
        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
        if (!$token){
            return new JsonResponse($responseError);
}
        /** @var Users $loginUser */
        $loginUser = $token->getUser();
        
        if  (!$loginUser->getId() === $contact->getAppUserSend()->getId()) {

            return new JsonResponse($responseError);
        }

        $data = json_decode($request->getContent(), true);

        $contact = new Contacts();
        $contact->setObject($data['object']);
        $contact->setSendDate(new \DateTime());
        $contact->setAppUserSend($data['app_user_send_id']);//      TODO:   créer le l'apelant 
        $contact->setAppUserReceive($data['app_user_receive_id']); // a changer

        $entityManager->persist($contact);
        $entityManager->flush();

        $contactArray = [
            'id' => $contact->getId(),
            'send_date' => $contact->getSendDate()->format('d-m-y H:i:s'),
            'object' => $contact->getObject(),
            'app_user_send_id' => $contact->getAppUserSend()->getId(),
            'app_user_receive_id' => $contact->getAppUserReceive()->getId(),
            'relation_status' => $contact->getRelationStatus()->getId(),
            // Définissez les autres propriétés de l'objet contact
        ];
        $contactJson = json_encode($contactArray);
        return new JsonResponse($contactJson, 200, [], true);
    }
}