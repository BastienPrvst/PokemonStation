<?php

namespace App\Entity;

use App\Repository\CapturedPokemonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CapturedPokemonRepository::class)]
class CapturedPokemon
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



}
