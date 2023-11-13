<?php

namespace App\Entity;

use App\Repository\DomainsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DomainsRepository::class)]
class Domains
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: ResearchCenters::class, inversedBy: 'domains')]
    private Collection $researchCenters;

    #[ORM\ManyToMany(targetEntity: Researchers::class, mappedBy: 'domains')]
    private Collection $researchers;

    public function __construct()
    {
        $this->researchCenters = new ArrayCollection();
        $this->researchers = new ArrayCollection();
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

    /**
     * @return Collection<int, Researchers>
     */
    public function getResearchers(): Collection
    {
        return $this->researchers;
    }

    public function addResearcher(Researchers $researcher): static
    {
        if (!$this->researchers->contains($researcher)) {
            $this->researchers->add($researcher);
            $researcher->addDomaine($this);
        }

        return $this;
    }

    public function removeResearcher(Researchers $researcher): static
    {
        if ($this->researchers->removeElement($researcher)) {
            $researcher->removeDomaine($this);
        }

        return $this;
    }
}
