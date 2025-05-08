<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée !')]
#[UniqueEntity(fields: 'pseudonym', message: 'Ce pseudonyme est déjà utilisé !')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;
    #[ORM\Column]
    private array $roles = [];
/**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;
    #[ORM\Column(length: 20, unique: true)]
    private ?string $pseudonym = null;
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: CapturedPokemon::class, fetch: 'EAGER', orphanRemoval: true)]
    private Collection $capturedPokemon;
    #[ORM\Column]
    private ?int $launchs = null;
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $lastObtainedLaunch = null;
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $avatar = null;
    #[ORM\Column(nullable: true)]
    private ?int $money = null;
    #[ORM\Column(nullable: true)]
    private ?int $launch_count = null;
    private Collection $friends;
    #[ORM\OneToMany(mappedBy: 'friendA', targetEntity: Friendship::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $friendsA;
    #[ORM\OneToMany(mappedBy: 'friendB', targetEntity: Friendship::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $friendsB;

    /**
     * @var Collection<int, UserItems>
     */
    #[ORM\OneToMany(
        mappedBy: 'user',
        targetEntity: UserItems::class,
        cascade: ['persist', 'remove'],
        fetch: "EAGER",
        orphanRemoval: true
    )]
    #[Ignore]
    private Collection $userItems;

    #[ORM\Column(nullable: true)]
    private ?int $score = null;

    public function __construct()
    {
        $this->capturedPokemon = new ArrayCollection();
        $this->friends = new ArrayCollection();
        $this->userItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
//         $this->plainPassword = null;
    }

    public function getPseudonym(): ?string
    {
        return $this->pseudonym;
    }

    public function setPseudonym(string $pseudonym): self
    {
        $this->pseudonym = $pseudonym;
        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return Collection<int, CapturedPokemon>
     */
    public function getCapturedPokemon(): Collection
    {
        return $this->capturedPokemon;
    }

    public function addCapturedPokemon(CapturedPokemon $capturedPokemon): self
    {
        if (!$this->capturedPokemon->contains($capturedPokemon)) {
            $this->capturedPokemon->add($capturedPokemon);
            $capturedPokemon->setOwner($this);
        }

        return $this;
    }

    public function removeCapturedPokemon(CapturedPokemon $capturedPokemon): self
    {
        if ($this->capturedPokemon->removeElement($capturedPokemon)) {
            // set the owning side to null (unless already changed)
            if ($capturedPokemon->getOwner() === $this) {
                $capturedPokemon->setOwner(null);
            }
        }

        return $this;
    }

    public function getLaunchs(): ?int
    {
        return $this->launchs;
    }

    public function setLaunchs(int $launchs): self
    {
        $this->launchs = $launchs;
        return $this;
    }

    public function getLastObtainedLaunch(): ?\DateTimeInterface
    {
        return $this->lastObtainedLaunch;
    }

    public function setLastObtainedLaunch(\DateTimeInterface $lastObtainedLaunch): self
    {
        $this->lastObtainedLaunch = $lastObtainedLaunch;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getMoney(): ?int
    {
        return $this->money;
    }

    public function setMoney(?int $money): self
    {
        $this->money = $money;
        return $this;
    }

    public function getLaunchCount(): ?int
    {
        return $this->launch_count;
    }

    public function setLaunchCount(?int $launch_count): self
    {
        $this->launch_count = $launch_count;
        return $this;
    }

    /**
     * @return Collection<int, Friendship>
     */
    public function getFriendships(): Collection
    {
        $this->friends = new ArrayCollection(array_merge($this->friendsA->toArray(), $this->friendsB->toArray()));
        return $this->friends;
    }

    public function addFriendship(User $newFriendship): self
    {
        $this->friends = $this->getFriendships();
        $friend = new Friendship();
        if (!$this->friends->contains($friend)) {
            $this->friends->add($friend);
            $friend->setFriendA($this);
            $friend->setFriendB($newFriendship);
        }

        return $this;
    }

    public function removeFriendship(Friendship $friend): self
    {
        $this->friends->removeElement($friend);
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
            $userItem->setUser($this);
        }

        return $this;
    }

    public function removeUserItem(UserItems $userItem): static
    {
        if ($this->userItems->removeElement($userItem)) {
            // set the owning side to null (unless already changed)
            if ($userItem->getUser() === $this) {
                $userItem->setUser(null);
            }
        }

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): static
    {
        $this->score = $score;

        return $this;
    }
}
