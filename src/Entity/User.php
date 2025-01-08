<?php
// Table de donées des utilisateurs du site
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée !')]
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

    #[ORM\Column(length: 50)]
    private ?string $pseudonym = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: CapturedPokemon::class, orphanRemoval: true)]
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

    #[ORM\Column(nullable: true)]
    private ?int $hyper_ball = null;

    #[ORM\Column(nullable: true)]
    private ?int $shiny_ball = null;

    #[ORM\Column(nullable: true)]
    private ?int $master_ball = null;


    public function __construct()
    {
        $this->capturedPokemon = new ArrayCollection();
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
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getHyperBall(): ?int
    {
        return $this->hyper_ball;
    }

    public function setHyperBall(?int $hyper_ball): self
    {
        $this->hyper_ball = $hyper_ball;

        return $this;
    }

    public function getShinyBall(): ?int
    {
        return $this->shiny_ball;
    }

    public function setShinyBall(?int $shiny_ball): self
    {
        $this->shiny_ball = $shiny_ball;

        return $this;
    }

    public function getMasterBall(): ?int
    {
        return $this->master_ball;
    }

    public function setMasterBall(?int $master_ball): self
    {
        $this->master_ball = $master_ball;

        return $this;
    }



    
}
