<?php

namespace App\Serializer;

use App\Entity\Users;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Security\Voter\ContactVoter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomObjectNormalizer extends ObjectNormalizer
{
    private $tokenStorage;
    private $contactVoter;

    public function __construct(TokenStorageInterface $tokenStorage, ContactVoter $contactVoter)
    {
        parent::__construct();
        $this->tokenStorage = $tokenStorage;
        $this->contactVoter = $contactVoter;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if ($object instanceof Users) {
            $ignoredAttributes = [];

            if (isset($context['ignore_user_password']) && $context['ignore_user_password']) {
                $ignoredAttributes[] = 'password';
            }

            if (isset($context['ignore_user_contacts']) && $context['ignore_user_contacts']) {
                $ignoredAttributes[] = "contactsSend";
                $ignoredAttributes[] = "contactsReceive";
            }

            if (isset($context['ignore_user_roles']) && $context['ignore_user_roles']) {
                $ignoredAttributes[] = 'roles';
            }

            if (isset($context['ignore_user_email_unless_accepted_contact_or_same_user']) && $context['ignore_user_email_unless_accepted_contact_or_same_user']) {
                $token = $this->tokenStorage->getToken();
                /** @var Users? $loggedUser */
                $loggedInUser = $token ? $token->getUser() : null;
                $privacySecurity = (
                    $token && (
                        ($loggedInUser->getId() === $object->getId()) ||
                        $this->contactVoter->voteOnAttribute('HAS_ACCEPTED_CONTACT', $object, $token)
                    )
                );
                if (!$privacySecurity) {
                    $ignoredAttributes[] = 'email';
                }
                if (!($token && $loggedInUser->getId() === $object->getId())){
                    $ignoredAttributes[] = 'location';
                }
            }

            $context[AbstractObjectNormalizer::IGNORED_ATTRIBUTES] = $ignoredAttributes;
        }
        $data = parent::normalize($object, $format, $context);

        if (!is_array($data) && !($data instanceof \Traversable)) {
            return $data;
        }

        $snakeCasedData = [];
        foreach ($data as $key => $value) {
            $snakeCasedKey = lcfirst(preg_replace_callback('/[A-Z]/', function ($matches) {
                return '_' . strtolower($matches[0]);
            }, $key));
            $snakeCasedData[$snakeCasedKey] = $value;
        }

        return $snakeCasedData;
    }
}
