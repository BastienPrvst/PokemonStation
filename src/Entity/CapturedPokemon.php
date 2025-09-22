<?php

namespace App\Entity;

use App\Repository\CapturedPokemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CapturedPokemonRepository::class)]
class CapturedPokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getTrade"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'capturedPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getTrade"])]
    private ?User $owner = null;

    #[ORM\ManyToOne(fetch: "EAGER", inversedBy: 'capturedPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getTrade"])]
    private ?Pokemon $pokemon = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getTrade"])]
    private ?\DateTimeInterface $captureDate = null;

    #[ORM\Column]
    #[Groups(["getTrade"])]
    private ?bool $shiny = null;

    #[ORM\Column]
    #[Groups(["getTrade"])]
    private ?int $timesCaptured = null;

    /**
     * @var Collection<int, Trade>
     */
    #[ORM\OneToMany(mappedBy: 'pokemonTrade1', targetEntity: Trade::class, cascade: ['remove'])]
    private Collection $pokeTrade1;

    /**
     * @var Collection<int, Trade>
     */
    #[ORM\OneToMany(mappedBy: 'pokemonTrade2', targetEntity: Trade::class, cascade: ['remove'])]
    private Collection $pokeTrade2;

    #[ORM\Column]
    private ?int $quantity = null;

	private bool $inPossession = false;

    public function __construct()
    {
        $this->pokeTrade1 = new ArrayCollection();
        $this->pokeTrade2 = new ArrayCollection();
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
    public function getPokeTrade1(): Collection
    {
        return $this->pokeTrade1;
    }

    public function addPokeTrade1(Trade $pokeTrade1): static
    {
        if (!$this->pokeTrade1->contains($pokeTrade1)) {
            $this->pokeTrade1->add($pokeTrade1);
            $pokeTrade1->setPokemonTrade1($this);
        }

        return $this;
    }

    public function removePokeTrade1(Trade $pokeTrade1): static
    {
        if ($this->pokeTrade1->removeElement($pokeTrade1)) {
            // set the owning side to null (unless already changed)
            if ($pokeTrade1->getPokemonTrade1() === $this) {
                $pokeTrade1->setPokemonTrade1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trade>
     */
    public function getPokeTrade2(): Collection
    {
        return $this->pokeTrade2;
    }

    public function addPokeTrade2(Trade $pokeTrade2): static
    {
        if (!$this->pokeTrade2->contains($pokeTrade2)) {
            $this->pokeTrade2->add($pokeTrade2);
            $pokeTrade2->setPokemonTrade2($this);
        }

        return $this;
    }

    public function removePokeTrade2(Trade $pokeTrade2): static
    {
        if ($this->pokeTrade2->removeElement($pokeTrade2)) {
            // set the owning side to null (unless already changed)
            if ($pokeTrade2->getPokemonTrade2() === $this) {
                $pokeTrade2->setPokemonTrade2(null);
            }
        }

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

	public function isInPossession(): ?bool
	{
		return $this->inPossession;
	}

	public function setInPossession(bool $inPossession): self
	{
		$this->inPossession = $inPossession;
		return $this;
	}
}
