<?php 

namespace App\DTO;

use App\Entity\Pokemon;

final class CapturedPokemonDTO extends PokemonDTO
{

    public bool $captured = false;

    public bool $altCaptured = false;

    public bool $shiny = false;

    public bool $onlyShiny = false;

    public bool $altShiny = false;

    public function __construct(Pokemon $pokemon)
    {
        parent::__construct($pokemon);
    }

}