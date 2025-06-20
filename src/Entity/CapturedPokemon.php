<?php

namespace App\Entity;

use App\Repository\CapturedPokemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CapturedPokemonRepository::class)]
class CapturedPokemon extends \App\Entity\Pokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'capturedPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToOne(fetch: "EAGER", inversedBy: 'capturedPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $pokemon = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $captureDate = null;

    #[ORM\Column]
    private ?bool $shiny = null;

    #[ORM\Column]
    private ?int $timesCaptured = null;

    /**
     * @var Collection<int, Trade>
     */
    #[ORM\OneToMany(mappedBy: 'tradePoke1', targetEntity: Trade::class)]
    private Collection $tradePoke1;

    /**
     * @var Collection<int, Trade>
     */
    #[ORM\OneToMany(mappedBy: 'tradePoke2', targetEntity: Trade::class)]
    private Collection $tradePoke2;

    public function __construct()
    {
        $this->tradePoke1 = new ArrayCollection();
        $this->tradePoke2 = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(?Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getCaptureDate(): ?\DateTimeInterface
    {
        return $this->captureDate;
    }

    public function setCaptureDate(\DateTimeInterface $captureDate): self
    {
        $this->captureDate = $captureDate;

        return $this;
    }

    public function getShiny(): ?bool
    {
        return $this->shiny;
    }

    public function setShiny(bool $shiny): self
    {
        $this->shiny = $shiny;

        return $this;
    }

    public function getTimesCaptured(): ?int
    {
        return $this->timesCaptured;
    }

    public function setTimesCaptured(int $timesCaptured): static
    {
        $this->timesCaptured = $timesCaptured;

        return $this;
    }

    /**
     * @return Collection<int, Trade>
     */
    public function getTradePoke1(): Collection
    {
        return $this->tradePoke1;
    }

    public function addTradePoke1(Trade $tradePoke1): static
    {
        if (!$this->tradePoke1->contains($tradePoke1)) {
            $this->tradePoke1->add($tradePoke1);
            $tradePoke1->setTradePoke1($this);
        }

        return $this;
    }

    public function removeTradePoke1(Trade $tradePoke1): static
    {
        if ($this->tradePoke1->removeElement($tradePoke1)) {
            // set the owning side to null (unless already changed)
            if ($tradePoke1->getTradePoke1() === $this) {
                $tradePoke1->setTradePoke1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trade>
     */
    public function getTradePoke2(): Collection
    {
        return $this->tradePoke2;
    }

    public function addTradePoke2(Trade $tradePoke2): static
    {
        if (!$this->tradePoke2->contains($tradePoke2)) {
            $this->tradePoke2->add($tradePoke2);
            $tradePoke2->setTradePoke2($this);
        }

        return $this;
    }

    public function removeTradePoke2(Trade $tradePoke2): static
    {
        if ($this->tradePoke2->removeElement($tradePoke2)) {
            // set the owning side to null (unless already changed)
            if ($tradePoke2->getTradePoke2() === $this) {
                $tradePoke2->setTradePoke2(null);
            }
        }

        return $this;
    }



}
