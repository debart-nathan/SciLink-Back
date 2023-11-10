<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column]
    private ?int $postal_code = null;

    #[ORM\Column(length: 255)]
    private ?string $commune = null;

    #[ORM\OneToMany(mappedBy: 'located', targetEntity: ResearchCenters::class)]
    private Collection $researchCenters;

    public function __construct()
    {
        $this->researchCenters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?int
    {
        return $this->postal_code;
    }

    public function setPostalCode(int $postal_code): static
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(string $commune): static
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * @return Collection<int, ResearchCenter>
     */
    public function getResearchCenters(): Collection
    {
        return $this->researchCenters;
    }

    public function addResearchCenter(ResearchCenters $researchCenter): static
    {
        if (!$this->researchCenters->contains($researchCenter)) {
            $this->researchCenters->add($researchCenter);
            $researchCenter->setLocated($this);
        }

        return $this;
    }

    public function removeResearchCenter(ResearchCenters $researchCenter): static
    {
        if ($this->researchCenters->removeElement($researchCenter)) {
            // set the owning side to null (unless already changed)
            if ($researchCenter->getLocated() === $this) {
                $researchCenter->setLocated(null);
            }
        }

        return $this;
    }
}
