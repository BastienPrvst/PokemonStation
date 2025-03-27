<?php

namespace App\Entity;

use App\Repository\UserItemsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserItemsRepository::class)]
class UserItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(fetch: "EAGER", inversedBy: 'userItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userId = null;

    #[ORM\ManyToOne(fetch: "EAGER", inversedBy: 'userItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Items $itemId = null;

    #[ORM\Column]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getItemId(): ?Items
    {
        return $this->itemId;
    }

    public function setItemId(?Items $itemId): static
    {
        $this->itemId = $itemId;

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
}
