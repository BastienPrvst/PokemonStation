<?php

namespace App\Entity;

use App\Repository\GenerationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GenerationRepository::class)]
class Generation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $genNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $genRegion = null;

    #[ORM\OneToMany(mappedBy: 'gen', targetEntity: Pokemon::class, orphanRemoval: true)]
    #[ORM\OrderBy(['pokeId' => 'ASC'])]
    private Collection $pokemon;

    public function __construct()
    {
        $this->pokemon = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGenNumber(): ?int
    {
        return $this->genNumber;
    }

    public function setGenNumber(int $genNumber): self
    {
        $this->genNumber = $genNumber;

        return $this;
    }

    public function getGenRegion(): ?string
    {
        return $this->genRegion;
    }

    public function setGenRegion(string $genRegion): self
    {
        $this->genRegion = $genRegion;

        return $this;
    }

    /**
     * @return Collection<int, Pokemon>
     */
    public function getPokemon(): Collection
    {
        return $this->pokemon;
    }

    public function addPokemon(Pokemon $pokemon): self
    {
        if (!$this->pokemon->contains($pokemon)) {
            $this->pokemon->add($pokemon);
            $pokemon->setGen($this);
        }

        return $this;
    }

    public function removePokemon(Pokemon $pokemon): self
    {
        if ($this->pokemon->removeElement($pokemon)) {
            // set the owning side to null (unless already changed)
            if ($pokemon->getGen() === $this) {
                $pokemon->setGen(null);
            }
        }

        return $this;
    }
}
