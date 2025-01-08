<?php

namespace App\Entity;

use App\Repository\PokemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: PokemonRepository::class)]
class Pokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type2 = null;

    #[ORM\Column(length: 5000)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'pokemon', targetEntity: CapturedPokemon::class, orphanRemoval: true)]
    private Collection $capturedPokemon;

    public function __construct()
    {
        $this->capturedPokemon = new ArrayCollection();
    }




    #[ORM\Column(length: 50)]
    private ?string $gif = null;

    #[ORM\Column(length: 50)]
    private ?string $name_en = null;

    #[ORM\Column(length: 30)]
    private ?string $rarity = null;

    #[ORM\Column]
    private ?int $pokeId = null;


    

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType2(): ?string
    {
        return $this->type2;
    }

    public function setType2(?string $type2): self
    {
        $this->type2 = $type2;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
            $capturedPokemon->setPokemon($this);
        }

        return $this;
    }


    public function getGif(): ?string
    {
        return $this->gif;
    }

    public function setGif(string $gif): self
    {
        $this->gif = $gif;

        return $this;

    }

    public function removeCapturedPokemon(CapturedPokemon $capturedPokemon): self
    {
        if ($this->capturedPokemon->removeElement($capturedPokemon)) {
            // set the owning side to null (unless already changed)
            if ($capturedPokemon->getPokemon() === $this) {
                $capturedPokemon->setPokemon(null);
            }
        }


        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->name_en;
    }

    public function setNameEn(string $name_en): self
    {
        $this->name_en = $name_en;

        return $this;
    }

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function setRarity(string $rarity): self
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getPokeId(): ?int
    {
        return $this->pokeId;
    }

    public function setPokeId(int $pokeId): self
    {
        $this->pokeId = $pokeId;

        return $this;
    }




}





