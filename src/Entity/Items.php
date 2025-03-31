<?php

namespace App\Entity;

use App\Repository\ItemsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: ItemsRepository::class)]
class Items
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private array $stats = [];

    #[ORM\Column(length: 500)]
    private ?string $image = null;

    #[ORM\Column]
    private ?bool $active = false;

    /**
     * @var Collection<int, UserItems>
     */
    #[ORM\OneToMany(
        mappedBy: 'item',
        targetEntity: UserItems::class,
        cascade: ['persist', 'remove'],
        fetch: "EAGER",
        orphanRemoval: true
    )]
    #[Ignore]
    private Collection $userItems;

    public function __construct()
    {
        $this->userItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStats(): ?array
    {
        return $this->stats;
    }

    public function setStats(?array $stats): self
    {
        $this->stats = $stats;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, UserItems>
     */
    public function getUserItems(): Collection
    {
        return $this->userItems;
    }

    public function addUserItem(UserItems $userItem): static
    {
        if (!$this->userItems->contains($userItem)) {
            $this->userItems->add($userItem);
            $userItem->setItem($this);
        }

        return $this;
    }

    public function removeUserItem(UserItems $userItem): static
    {
        if ($this->userItems->removeElement($userItem)) {
            // set the owning side to null (unless already changed)
            if ($userItem->getItem() === $this) {
                $userItem->setItem(null);
            }
        }

        return $this;
    }
}
