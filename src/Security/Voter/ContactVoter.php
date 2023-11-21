<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Users;
use App\Repository\ContactsRepository;
use App\Repository\RelationStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ContactVoter extends Voter
{
    private $security;
    private $entityManager;
    private $contactsRepository;
    private $relationStatusRepository;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        ContactsRepository $contactsRepository,
        RelationStatusRepository $relationStatusRepository
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->contactsRepository = $contactsRepository;
        $this->relationStatusRepository = $relationStatusRepository;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['HAS_ACCEPTED_CONTACT'])
            && $subject instanceof Users;
    }

    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // si l’utilisateur n'est pas connecté envoyer faux
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($attribute === 'HAS_ACCEPTED_CONTACT') {

            $acceptedStatus = $this->relationStatusRepository->findOneBy(['status' => 'accepted']);

           // vérifie si l'utilisateur connecté a envoyer une demande de contact qui a était accepté par le sujet
            $contactAsSender = $this->contactsRepository->findOneBy([
                'app_user_send' => $user,
                'app_user_recive' => $subject,
                'relationStatus' => $acceptedStatus
            ]);

            // vérifie si le sujet a envoyer une demande de contact qui a était accepté par l'utilisateur connecté
            $contactAsReceiver = $this->contactsRepository->findOneBy([
                'app_user_send' => $subject,
                'app_user_recive' => $user,
                'relationStatus' => $acceptedStatus
            ]);

            return $contactAsSender !== null || $contactAsReceiver !== null;
        }

        return false;
    }
}
