<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $user_name = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255)]
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\OneToOne(inversedBy: 'users', cascade: ['persist', 'remove'])]
    private ?Researchers $researcher = null;

    #[ORM\ManyToMany(targetEntity: ResearchCenters::class, inversedBy: 'users')]
    private Collection $researchCenters;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Locations $location = null;

    #[ORM\OneToMany(mappedBy: 'app_user', targetEntity: Investors::class)]
    private Collection $investors;

    #[ORM\OneToMany(mappedBy: 'app_user_send', targetEntity: Contacts::class, orphanRemoval: true)]
    private Collection $contacts_send;

    #[ORM\OneToMany(mappedBy: 'app_user_receive', targetEntity: Contacts::class, orphanRemoval: true)]
    private Collection $contacts_recive;

    public function __construct()
    {
        $this->researchCenters = new ArrayCollection();
        $this->investors = new ArrayCollection();
        $this->contacts_send = new ArrayCollection();
        $this->contacts_recive = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserName(): ?string
    {
        return $this->user_name;
    }

    public function setUserName(string $user_name): static
    {
        $this->user_name = $user_name;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->user_name;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getResearcher(): ?Researchers
    {
        return $this->researcher;
    }

    public function setResearcher(?Researchers $researcher): static
    {
        $this->researcher = $researcher;

        return $this;
    }

    /**
     * @return Collection<int, ResearchCenters>
     */
    public function getResearchCenters(): Collection
    {
        return $this->researchCenters;
    }

    public function addResearchCenter(ResearchCenters $researchCenter): static
    {
        if (!$this->researchCenters->contains($researchCenter)) {
            $this->researchCenters->add($researchCenter);
        }

        return $this;
    }

    public function removeResearchCenter(ResearchCenters $researchCenter): static
    {
        $this->researchCenters->removeElement($researchCenter);

        return $this;
    }

    public function getLocation(): ?Locations
    {
        return $this->location;
    }

    public function setLocation(?Locations $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Investors>
     */
    public function getInvestors(): Collection
    {
        return $this->investors;
    }

    public function addInvestor(Investors $investor): static
    {
        if (!$this->investors->contains($investor)) {
            $this->investors->add($investor);
            $investor->setAppUser($this);
        }

        return $this;
    }

    public function removeInvestor(Investors $investor): static
    {
        if ($this->investors->removeElement($investor)) {
            // set the owning side to null (unless already changed)
            if ($investor->getAppUser() === $this) {
                $investor->setAppUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Contacts>
     */
    public function getContactsSend(): Collection
    {
        return $this->contacts_send;
    }

    public function addContactsSend(Contacts $contactsSend): static
    {
        if (!$this->contacts_send->contains($contactsSend)) {
            $this->contacts_send->add($contactsSend);
            $contactsSend->setAppUserSend($this);
        }

        return $this;
    }

    public function removeContactsSend(Contacts $contactsSend): static
    {
        if ($this->contacts_send->removeElement($contactsSend)) {
            // set the owning side to null (unless already changed)
            if ($contactsSend->getAppUserSend() === $this) {
                $contactsSend->setAppUserSend(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Contacts>
     */
    public function getContactsReceive(): Collection
    {
        return $this->contacts_recive;
    }

    public function addContactsReceive(Contacts $contactsRecive): static
    {
        if (!$this->contacts_recive->contains($contactsRecive)) {
            $this->contacts_recive->add($contactsRecive);
            $contactsRecive->setAppUserRecive($this);
        }

        return $this;
    }

    public function removeContactsReceive(Contacts $contactsRecive): static
    {
        if ($this->contacts_recive->removeElement($contactsRecive)) {
            // set the owning side to null (unless already changed)
            if ($contactsRecive->getAppUserRecive() === $this) {
                $contactsRecive->setAppUserRecive(null);
            }
        }

        return $this;
    }
}
