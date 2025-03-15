<?php

namespace App\Entity;

use App\Repository\FriendshipRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FriendshipRepository::class)]
#[ORM\UniqueConstraint(
    name: "unique_friendship",
    columns: ["friend_a_id", "friend_b_id"]
)]
class Friendship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $friendA;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotEqualTo(propertyPath: "friendA", message: "Vous ne pouvez pas être ami avec vous-même.")]
    private User $friendB;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private bool $accepted = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFriendA(): User
    {
        return $this->friendA;
    }

    public function setFriendA(User $friendA): self
    {
        $this->friendA = $friendA;

        return $this;
    }

    public function getFriendB(): User
    {
        return $this->friendB;
    }

    public function setFriendB(User $friendB): self
    {
        $this->friendB = $friendB;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createAt): self
    {
        $this->createdAt = $createAt;

        return $this;
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }
}
