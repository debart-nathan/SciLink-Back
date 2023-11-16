<?php

namespace App\Service;

use App\Entity\Users;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Email;


class EmailService
{
    private $mailer;
    private $params;

    public function __construct(MailerInterface $mailer, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->params = $params;
    }

    public function sendEmail($sender, $recipient, $subject, $message)
    {

        $email = (new Email())
            ->from($this->params->get('company_email'))
            ->replyTo($sender)
            ->to($recipient)
            ->subject('SciLink : ' + $subject)
            ->text($message);

        $this->mailer->send($email);
    }

    public function sendRelationRequest(
        Users $userSend,
        Users $userReceive
    ) {
        $recipientEmail = $userReceive->getEmail();

        $sender = $userSend->getEmail();
        $subject = 'Demande de prise de contact de ' . $userSend->getUsername();
        $message = 'L\'utilisateur ' . $userSend->getUsername() . ' souhaite prendre contact avec vous.';

        $this->sendEmail($sender, $recipientEmail, $subject, $message);

        return [];
    }
}
