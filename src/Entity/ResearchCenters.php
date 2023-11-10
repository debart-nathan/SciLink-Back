<?php

namespace App\Entity;

use App\Repository\ResearchCenterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResearchCenterRepository::class)]
class ResearchCenters
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libele = null;

    #[ORM\Column(length: 255)]
    private ?string $sigle = null;

    #[ORM\Column]
    private ?int $founding_year = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    #[ORM\Column(length: 255)]
    private ?string $website = null;

    #[ORM\Column(length: 255)]
    private ?string $fiche_msr = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'researchCenters')]
    private Collection $parent;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $researchCenters;

    #[ORM\ManyToOne(inversedBy: 'researchCenters')]
    private ?Location $located = null;

    #[ORM\OneToMany(mappedBy: 'ResearchCenters', targetEntity: Manages::class, orphanRemoval: true)]
    private Collection $manages;

    #[ORM\OneToMany(mappedBy: 'researchCenter', targetEntity: Tutelles::class, orphanRemoval: true)]
    private Collection $tutelles;

    #[ORM\ManyToMany(targetEntity: Domaines::class, mappedBy: 'researchCenters')]
    private Collection $domaines;

    public function __construct()
    {
        $this->parent = new ArrayCollection();
        $this->researchCenters = new ArrayCollection();
        $this->manages = new ArrayCollection();
        $this->tutelles = new ArrayCollection();
        $this->domaines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibele(): ?string
    {
        return $this->libele;
    }

    public function setLibele(string $libele): static
    {
        $this->libele = $libele;

        return $this;
    }

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(string $sigle): static
    {
        $this->sigle = $sigle;

        return $this;
    }

    public function getFoundingYear(): ?int
    {
        return $this->founding_year;
    }

    public function setFoundingYear(int $founding_year): static
    {
        $this->founding_year = $founding_year;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getFicheMsr(): ?string
    {
        return $this->fiche_msr;
    }

    public function setFicheMsr(string $fiche_msr): static
    {
        $this->fiche_msr = $fiche_msr;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getParent(): Collection
    {
        return $this->parent;
    }

    public function addParent(self $parent): static
    {
        if (!$this->parent->contains($parent)) {
            $this->parent->add($parent);
        }

        return $this;
    }

    public function removeParent(self $parent): static
    {
        $this->parent->removeElement($parent);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getResearchCenters(): Collection
    {
        return $this->researchCenters;
    }

    public function addResearchCenter(self $researchCenter): static
    {
        if (!$this->researchCenters->contains($researchCenter)) {
            $this->researchCenters->add($researchCenter);
            $researchCenter->addParent($this);
        }

        return $this;
    }

    public function removeResearchCenter(self $researchCenter): static
    {
        if ($this->researchCenters->removeElement($researchCenter)) {
            $researchCenter->removeParent($this);
        }

        return $this;
    }

    public function getLocated(): ?Location
    {
        return $this->located;
    }

    public function setLocated(?Location $located): static
    {
        $this->located = $located;

        return $this;
    }

    /**
     * @return Collection<int, Manages>
     */
    public function getManages(): Collection
    {
        return $this->manages;
    }

    public function addManage(Manages $manage): static
    {
        if (!$this->manages->contains($manage)) {
            $this->manages->add($manage);
            $manage->setResearchCenter($this);
        }

        return $this;
    }

    public function removeManage(Manages $manage): static
    {
        if ($this->manages->removeElement($manage)) {
            // set the owning side to null (unless already changed)
            if ($manage->getResearchCenter() === $this) {
                $manage->setResearchCenter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tutelles>
     */
    public function getTutelles(): Collection
    {
        return $this->tutelles;
    }

    public function addTutelle(Tutelles $tutelle): static
    {
        if (!$this->tutelles->contains($tutelle)) {
            $this->tutelles->add($tutelle);
            $tutelle->setResearchCenter($this);
        }

        return $this;
    }

    public function removeTutelle(Tutelles $tutelle): static
    {
        if ($this->tutelles->removeElement($tutelle)) {
            // set the owning side to null (unless already changed)
            if ($tutelle->getResearchCenter() === $this) {
                $tutelle->setResearchCenter(null);
            }
        }

        return $this;
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
            $domaine->addResearchCenter($this);
        }

        return $this;
    }

    public function removeDomaine(Domaines $domaine): static
    {
        if ($this->domaines->removeElement($domaine)) {
            $domaine->removeResearchCenter($this);
        }

        return $this;
    }
}
