<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ContactVoter extends Voter
{
    private $security;
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['HAS_ACCEPTED_CONTACT'])
            && $subject instanceof Users;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($attribute === 'HAS_ACCEPTED_CONTACT') {
            $contactRepository = $this->entityManager->getRepository(Contacts::class);
            $relationStatusRepository = $this->entityManager->getRepository(RelationStatus::class);
            $acceptedStatus = $relationStatusRepository->findOneBy(['status' => 'accepted']);

            // Check if there's an accepted contact where the authenticated user is the sender and the subject is the receiver
            $contactAsSender = $contactRepository->findOneBy([
                'app_user_send' => $user,
                'app_user_receive' => $subject,
                'relationStatus' => $acceptedStatus
            ]);

            // Check if there's an accepted contact where the authenticated user is the receiver and the subject is the sender
            $contactAsReceiver = $contactRepository->findOneBy([
                'app_user_send' => $subject,
                'app_user_receive' => $user,
                'relationStatus' => $acceptedStatus
            ]);

            return $contactAsSender !== null || $contactAsReceiver !== null;
        }

        return false;
    }
}
