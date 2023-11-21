<?php

namespace App\Entity;

use App\Repository\ManagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManagesRepository::class)]
class Manages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $grade = null;

    #[ORM\ManyToOne(inversedBy: 'manages')]
    #[ORM\JoinColumn(nullable: false,onDelete: 'CASCADE')]
    private ?Personnels $personnel = null;

    #[ORM\ManyToOne(inversedBy: 'manages')]
    #[ORM\JoinColumn(nullable: false,onDelete: 'CASCADE')]
    private ?ResearchCenters $researchCenter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(string $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getPersonnel(): ?Personnels
    {
        return $this->personnel;
    }

    public function setPersonnel(?Personnels $personnel): static
    {
        $this->personnel = $personnel;

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
