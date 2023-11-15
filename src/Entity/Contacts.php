<?php

namespace App\Entity;

use App\Repository\ContactsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactsRepository::class)]
class Contacts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $send_date = null;

    #[ORM\Column(length: 255)]
    private ?string $object = null;

    #[ORM\ManyToOne(inversedBy: 'contacts_send')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $app_user_send = null;

    #[ORM\ManyToOne(inversedBy: 'contacts_receive')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $app_user_receive = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    private ?RelationStatus $relationStatus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSendDate(): ?\DateTimeInterface
    {
        return $this->send_date;
    }

    public function setSendDate(\DateTimeInterface $send_date): static
    {
        $this->send_date = $send_date;

        return $this;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(string $object): static
    {
        $this->object = $object;

        return $this;
    }

    public function getAppUserSend(): ?Users
    {
        return $this->app_user_send;
    }

    public function setAppUserSend(?Users $app_user_send): static
    {
        $this->app_user_send = $app_user_send;

        return $this;
    }

    public function getAppUserReceive(): ?Users
    {
        return $this->app_user_receive;
    }

    public function setAppUserReceive(?Users $app_user_receive): static
    {
        $this->app_user_receive = $app_user_receive;

        return $this;
    }

    public function getRelationStatus(): ?RelationStatus
    {
        return $this->relationStatus;
    }

    public function setRelationStatus(?RelationStatus $relationStatus): static
    {
        $this->relationStatus = $relationStatus;

        return $this;
    }
}
