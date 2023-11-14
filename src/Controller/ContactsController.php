<?php

namespace App\Controller;

use App\Entity\Contacts;
use App\Repository\ContactsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactsController extends AbstractController
{
    #[Route('/contacts', name: 'app_contacts', methods: ['GET'])]
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
                'SendDate' => $contact->getSendDate()->format('d-m-y H:i:s'),
                'Object' => $contact->getObject(),
                'AppUserSend' => $contact->getAppUserSend()->getId(),
                'AppUserRecive' => $contact->getAppUserRecive()->getId(),
                'RelationStatus' => $contact->getRelationStatus()->getId(),
            ];
        }
        $contactsJson = json_encode($contactsArray);
        return new JsonResponse($contactsJson, 200, [], true);
    }

    #[Route('/contacts/{id}', name: 'app_contacts_show', methods: ['GET'])]
    public function show(ContactsRepository $contactRepository, Contacts $contact): JsonResponse
    {
        $contactArray = [
            'id' => $contact->getId(),
            'SendDate' => $contact->getSendDate()->format('d-m-y H:i:s'),
            'Object' => $contact->getObject(),
            'AppUserSend' => $contact->getAppUserSend()->getId(),
            'AppUserRecive' => $contact->getAppUserRecive()->getId(),
            'RelationStatus' => $contact->getRelationStatus()->getId(),
        ];
        $contactJson = json_encode($contactArray);
        return new JsonResponse($contactJson, 200, [], true);
    }

    #[Route('/contacts/{id}', name: 'app_contacts_update', methods: ['PATCH'])]
    public function update(ContactsRepository $contactRepository, Contacts $contact, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['SendDate'])) {
            $contact->setSendDate($data['SendDate']);
        }
        if (isset($data['Object'])) {
            $contact->setObject($data['Object']);
        }
        if (isset($data['AppUserSend'])) {
            $contact->setAppUserSend($data['AppUserSend']);
        }
        if (isset($data['AppUserReceive'])) {
            $contact->setAppUserRecive($data['AppUserRecive']);
        }
        if (isset($data['RelationStatus'])) {
            $contact->setRelationStatus($data['RelationStatus']);
        }

        $entityManager->persist($contact);
        $entityManager->flush();

        $contactArray = [
            'id' => $contact->getId(),
            'SendDate' => $contact->getSendDate()->format('d-m-y H:i:s'),
            'Object' => $contact->getObject(),
            'AppUserSend' => $contact->getAppUserSend()->getId(),
            'AppUserRecive' => $contact->getAppUserRecive()->getId(),
            'RelationStatus' => $contact->getRelationStatus()->getId(),
        ];
        $contactJson = json_encode($contactArray);
        return new JsonResponse($contactJson, 200, [], true);
    }
}