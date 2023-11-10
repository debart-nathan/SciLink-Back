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

    #[ORM\ManyToMany(targetEntity: Domaines::class, inversedBy: 'researchers')]
    private Collection $domaines;

    #[ORM\OneToOne(mappedBy: 'researcher', cascade: ['persist', 'remove'])]
    private ?Users $app_user = null;

    public function __construct()
    {
        $this->domaines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Domaines>
     */
    public function getDomaines(): Collection
    {
        return $this->domaines;
    }

    public function addDomaine(Domaines $domaine): static
    {
        if (!$this->domaines->contains($domaine)) {
            $this->domaines->add($domaine);
        }

        return $this;
    }

    public function removeDomaine(Domaines $domaine): static
    {
        $this->domaines->removeElement($domaine);

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
}
