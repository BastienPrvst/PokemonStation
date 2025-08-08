<?php

namespace App\Entity;

use App\Enum\TradeStatus;
use App\Enum\TradeUserStatus;
use App\Repository\TradeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TradeRepository::class)]
class Trade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getTrade"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'initiatedTrade')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getTrade"])]
    private ?User $user1 = null;

    #[ORM\ManyToOne(inversedBy: 'receivedTrade')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getTrade"])]
    private ?User $user2 = null;

    #[ORM\Column]
    #[Groups(["getTrade"])]
    private TradeStatus $status = TradeStatus::CREATED;

    #[ORM\Column]
    #[Groups(["getTrade"])]
    private TradeUserStatus $user1Status = TradeUserStatus::ONGOING;

    #[ORM\Column]
    #[Groups(["getTrade"])]
    private TradeUserStatus $user2Status = TradeUserStatus::ONGOING;

    #[ORM\ManyToOne(inversedBy: 'pokeTrade1')]
    #[Groups(["getTrade"])]
    private ?CapturedPokemon $pokemonTrade1 = null;

    #[ORM\ManyToOne(inversedBy: 'pokeTrade2')]
    #[Groups(["getTrade"])]
    private ?CapturedPokemon $pokemonTrade2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1(): ?User
    {
        return $this->user1;
    }

    public function setUser1(?User $user1): static
    {
        $this->user1 = $user1;

        return $this;
    }

    public function getUser2(): ?User
    {
        return $this->user2;
    }

    public function setUser2(?User $user2): static
    {
        $this->user2 = $user2;

        return $this;
    }

    public function getStatus(): TradeStatus
    {
        return $this->status;
    }

    public function setStatus(TradeStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUser1Status(): TradeUserStatus
    {
        return $this->user1Status;
    }

    public function setUser1Status(TradeUserStatus $user1Status): static
    {
        $this->user1Status = $user1Status;

        return $this;
    }

    public function getUser2Status(): TradeUserStatus
    {
        return $this->user2Status;
    }

    public function setUser2Status(TradeUserStatus $user2Status): static
    {
        $this->user2Status = $user2Status;

        return $this;
    }

    public function getPokemonTrade1(): ?CapturedPokemon
    {
        return $this->pokemonTrade1;
    }

    public function setPokemonTrade1(?CapturedPokemon $pokemonTrade1): static
    {
        $this->pokemonTrade1 = $pokemonTrade1;

        return $this;
    }

    public function getPokemonTrade2(): ?CapturedPokemon
    {
        return $this->pokemonTrade2;
    }

    public function setPokemonTrade2(?CapturedPokemon $pokemonTrade2): static
    {
        $this->pokemonTrade2 = $pokemonTrade2;

        return $this;
    }
}
