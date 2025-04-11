<?php

namespace App\Service;

use App\DTO\CapturedPokemonDTO;
use App\Entity\Pokemon;
use App\Entity\User;
use App\Repository\CapturedPokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CapturedPokemonService extends AbstractController
{
    public function __construct(
        private readonly CapturedPokemonRepository $capturedPokemonRepository,
    ) {}
    
    /**
     * userCapturedByGeneration
     *
     * @param  User $user
     * @param  Pokemon[] $pokemons
     * @return CapturedPokemonDTO[]
     */
    public function userCapturedByGeneration(User $user, array $pokemons): array
    {
        $capturedPokemons = $this->capturedPokemonRepository->findSpeciesCapturedByPokemon($user, $pokemons);
        $shinyCapturedPokemons = $this->capturedPokemonRepository->findShinyCapturedByPokemon($user, $pokemons);
        $altForms = [];
        $capturedPokemonsDTO = [];

        foreach ($pokemons as $pokemon) {

            if ($pokemon->getRelateTo()) {
                $altForms[] = $pokemon;

                continue;
            }

            $pokemonId = $pokemon->getPokeId();
            $captured = in_array($pokemonId, $capturedPokemons);
            $shiny = in_array($pokemonId, $shinyCapturedPokemons);
            $capturedPokemonsDTO[] = new CapturedPokemonDTO($pokemon, $captured || $shiny, $shiny);
        }

        foreach ($altForms as $altForm) {

            $pokemonRelateTo = $altForm->getRelateTo();
            $pokemonId = $altForm->getPokeId();

            $relatedCapturedPokemonDTO = array_filter(
                $capturedPokemonsDTO,
                fn(CapturedPokemonDTO $p) => $p->pokeId === $pokemonRelateTo->getPokeId()
            );

            $key = array_key_first($relatedCapturedPokemonDTO);
            $relatedCapturedPokemonDTO = $capturedPokemonsDTO[$key] ?? null;

            if (!$relatedCapturedPokemonDTO) continue;

            $captured = in_array($pokemonId, $capturedPokemons);
            $shiny = in_array($pokemonId, $shinyCapturedPokemons);
            $addCapture = $captured || $relatedCapturedPokemonDTO->captured;
            $addShiny = $shiny || $relatedCapturedPokemonDTO->shiny;

            $capturedPokemonsDTO[$key] = new CapturedPokemonDTO($pokemonRelateTo, $addCapture || $addShiny, $addShiny);
        }

        usort($capturedPokemonsDTO, fn(CapturedPokemonDTO $a, CapturedPokemonDTO $b) => $a->pokeId <=> $b->pokeId);

        return $capturedPokemonsDTO;
    }

}
