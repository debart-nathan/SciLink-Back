<?php

namespace App\Entity;

use App\Repository\InvestorsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvestorsRepository::class)]
class Investors
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sigle = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\OneToMany(mappedBy: 'investor', targetEntity: Tutelles::class)]
    private Collection $tutelles;

    #[ORM\ManyToOne(inversedBy: 'investors')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Users $app_user = null;

    public function __construct()
    {
        $this->tutelles = new ArrayCollection();
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

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(?string $sigle): static
    {
        $this->sigle = $sigle;

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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

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
            $tutelle->setInvestor($this);
        }

        return $this;
    }

    public function removeTutelle(Tutelles $tutelle): static
    {
        if ($this->tutelles->removeElement($tutelle)) {
            // set the owning side to null (unless already changed)
            if ($tutelle->getInvestor() === $this) {
                $tutelle->setInvestor(null);
            }
        }

        return $this;
    }

    public function getAppUser(): ?Users
    {
        return $this->app_user;
    }

    public function setAppUser(?Users $app_user): static
    {
        $this->app_user = $app_user;

        return $this;
    }
}
