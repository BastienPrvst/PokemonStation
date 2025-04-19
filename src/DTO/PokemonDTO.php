<?php 

namespace App\DTO;

use App\Entity\Pokemon;

class PokemonDTO
{

    public readonly ?int $id;

    public readonly ?string $name;

    public readonly ?string $type;

    public readonly ?string $type2;

    public readonly ?string $description;

    public readonly ?string $name_en;

    public readonly ?string $rarity;

    public readonly ?int $pokeId;

    public readonly bool $altForm;

    /** @var static[] $relatedPokemon */
    public array $relatedPokemon = [];

    public function __construct(Pokemon $pokemon)
    {
        $this->id = $pokemon->getId();
        $this->name = $pokemon->getName();
        $this->type = $pokemon->getType();
        $this->type2 = $pokemon->getType2();
        $this->description = $pokemon->getDescription();
        $this->name_en = $pokemon->getNameEn();
        $this->rarity = $pokemon->getRarity();
        $this->pokeId = $pokemon->getPokeId();
        $this->altForm = !!$pokemon->getRelateTo();

        if ($pokemon->getRelatedPokemon()?->count()) {
            $this->relatedPokemon = $pokemon->getRelatedPokemon()?->map(
                fn(Pokemon $p) => new self($p)
            )->getValues();
        }
    }

}