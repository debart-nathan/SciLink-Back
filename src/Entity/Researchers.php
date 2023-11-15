<?php

namespace App\Entity;

use App\Repository\ResearchersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResearchersRepository::class)]
class Researchers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Domains::class, inversedBy: 'researchers')]
    private Collection $domains;

    #[ORM\OneToOne(mappedBy: 'researcher', cascade: ['persist', 'remove'])]
    private ?Users $app_user = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    public function __construct()
    {
        $this->domains = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Domains>
     */
    public function getDomains(): Collection
    {
        return $this->domains;
    }

    public function addDomain(Domains $domain): static
    {
        if (!$this->domains->contains($domain)) {
            $this->domains->add($domain);
        }

        return $this;
    }

    public function removeDomain(Domains $domain): static
    {
        $this->domains->removeElement($domain);

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->app_user;
    }

    public function setUser(?Users $app_user): static
    {
        // unset the owning side of the relation if necessary
        if ($app_user === null && $this->app_user !== null) {
            $this->app_user->setResearcher(null);
        }

        // set the owning side of the relation if necessary
        if ($app_user !== null && $app_user->getResearcher() !== $this) {
            $app_user->setResearcher($this);
        }

        $this->app_user = $app_user;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

}