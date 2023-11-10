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
}
