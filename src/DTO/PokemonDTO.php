<?php 

namespace App\DTO;

use App\Entity\Pokemon;

readonly class PokemonDTO
{

    public ?int $id;

    public ?string $name;

    public ?string $type;

    public ?string $description;

    public ?string $name_en;

    public ?string $rarity;

    public ?int $pokeId;

    public ?self $relateTo;

    /** @var self[] $relatedPokemon */
    public array $relatedPokemon;

    public function __construct(Pokemon $pokemon)
    {
        $this->id = $pokemon->getId();
        $this->name = $pokemon->getName();
        $this->type = $pokemon->getType();
        $this->description = $pokemon->getDescription();
        $this->name_en = $pokemon->getNameEn();
        $this->rarity = $pokemon->getRarity();
        $this->pokeId = $pokemon->getPokeId();
        $this->relateTo = $pokemon->getRelateTo() ? new self($pokemon->getRelateTo()) : null;
    }

}