<?php

namespace App\Entity;

use App\Repository\PokemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokemonRepository::class)]
class Pokemon
{
    /** @var array<array-key, string> */
    public const RARITIES = ['C','PC','R','TR','ME','GMAX','SR','EX','UR'];

    /** @var array<array-key, string> */
    public const TYPES = [
        'acier',
        'combat',
        'dragon',
        'eau',
        'electrik',
        'fee',
        'feu',
        'glace',
        'insecte',
        'normal',
        'plante',
        'poison',
        'psy',
        'roche',
        'sol',
        'spectre',
        'tenebres',
        'vol',
    ];

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

    #[ORM\OneToMany(mappedBy: 'pokemon', targetEntity: CapturedPokemon::class, fetch: 'EAGER', orphanRemoval: true)]
    private Collection $capturedPokemon;

    #[ORM\Column(length: 50)]
    private ?string $name_en = null;

    #[ORM\Column(length: 30)]
    private ?string $rarity = null;

    #[ORM\Column]
    private ?int $pokeId = null;

    #[ORM\ManyToOne(targetEntity: self::class, cascade: ['persist'], inversedBy: 'relatedPokemon')]
    #[ORM\JoinColumn(name: 'relate_to_id', referencedColumnName: 'id', nullable: true)]
    private ?self $relateTo = null;

    #[ORM\OneToMany(mappedBy: 'relateTo', targetEntity: self::class)]
    private Collection $relatedPokemon;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'pokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Generation $gen = null;

    public function __construct()
    {
        $this->capturedPokemon = new ArrayCollection();
        $this->relatedPokemon = new ArrayCollection();
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

    public function getRelateTo(): ?self
    {
        return $this->relateTo;
    }

    public function setRelateTo(?self $relateTo): self
    {
        $this->relateTo = $relateTo;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getRelatedPokemon(): Collection
    {
        return $this->relatedPokemon;
    }

    public function addRelatedPokemon(self $relatedPokemon): self
    {
        if (!$this->relatedPokemon->contains($relatedPokemon)) {
            $this->relatedPokemon->add($relatedPokemon);
            $relatedPokemon->setRelateTo($this);
        }

        return $this;
    }

    public function removeRelatedPokemon(self $relatedPokemon): self
    {
        // set the owning side to null (unless already changed)
        if ($this->relatedPokemon->removeElement($relatedPokemon) && $relatedPokemon->getRelateTo() === $this) {
            $relatedPokemon->setRelateTo(null);
        }

        return $this;
    }

    public function getGen(): ?Generation
    {
        return $this->gen;
    }

    public function setGen(?Generation $gen): self
    {
        $this->gen = $gen;

        return $this;
    }
}
