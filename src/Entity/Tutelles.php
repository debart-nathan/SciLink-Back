<?php

namespace App\Entity;

use App\Repository\TutellesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TutellesRepository::class)]
class Tutelles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $uai = null;

    #[ORM\Column(length: 255)]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'tuteles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Investors $investor = null;

    #[ORM\ManyToOne(inversedBy: 'tuteles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ResearchCenters $researchCenter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUai(): ?string
    {
        return $this->uai;
    }

    public function setUai(string $uai): static
    {
        $this->uai = $uai;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getInvestor(): ?Investors
    {
        return $this->investor;
    }

    public function setInvestor(?Investors $investor): static
    {
        $this->investor = $investor;

        return $this;
    }

    public function getResearchCenter(): ?ResearchCenters
    {
        return $this->researchCenter;
    }

    public function setResearchCenter(?ResearchCenters $researchCenter): static
    {
        $this->researchCenter = $researchCenter;

        return $this;
    }
}
