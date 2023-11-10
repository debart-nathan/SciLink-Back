<?php

namespace App\Entity;

use App\Repository\DomainesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DomainesRepository::class)]
class Domaines
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: ResearchCenters::class, inversedBy: 'domaines')]
    private Collection $researchCenters;

    public function __construct()
    {
        $this->researchCenters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
}
