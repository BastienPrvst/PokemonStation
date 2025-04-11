<?php 

namespace App\DTO;

use App\Entity\Pokemon;

final readonly class CapturedPokemonDTO extends PokemonDTO
{

    public ?bool $captured;

    public ?bool $shiny;

    public function __construct(Pokemon $pokemon, bool $captured, bool $shiny)
    {
        parent::__construct($pokemon);

        $this->captured = $captured;
        $this->shiny = $shiny;
    }

}