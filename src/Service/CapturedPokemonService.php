<?php

namespace App\Service;

use App\DTO\CapturedPokemonDTO;
use App\DTO\PokemonDTO;
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
     * @param  User $user
     * @param  Pokemon[] $pokemons
     * @param  bool $hideRelated
     * @return CapturedPokemonDTO[]
     */
    public function userCapturedByGeneration(User $user, array $pokemons, $hideRelated = true): array
    {
        $capturedPokemons = $this->capturedPokemonRepository->findSpeciesCapturedByPokemon($user, $pokemons);
        $shinyCapturedPokemons = $this->capturedPokemonRepository->findShinyCapturedByPokemon($user, $pokemons);
        $altForms = [];
        $capturedPokemonsDTO = [];

        foreach ($pokemons as $pokemon) {

            if ($hideRelated && $pokemon->getRelateTo()) {
                $altForms[] = $pokemon;

                continue;
            }

            $pokemonId = $pokemon->getPokeId();
            $captured = in_array($pokemonId, $capturedPokemons);
            $shiny = in_array($pokemonId, $shinyCapturedPokemons);
            $capturedPokemonDTO = new CapturedPokemonDTO($pokemon);
            $capturedPokemonDTO->captured = $captured || $shiny;
            $capturedPokemonDTO->shiny = $shiny;
            $capturedPokemonDTO->onlyShiny = !$captured && $shiny;
            $capturedPokemonsDTO[] = $capturedPokemonDTO;
        }

        foreach ($altForms as $altForm) {

            $pokemonRelateTo = $altForm->getRelateTo();
            $pokemonId = $altForm->getPokeId();

            $basePokemonDTO = array_filter(
                $capturedPokemonsDTO,
                fn(CapturedPokemonDTO $p) => $p->pokeId === $pokemonRelateTo->getPokeId()
            );

            $key = array_key_first($basePokemonDTO);
            $basePokemonDTO = $capturedPokemonsDTO[$key] ?? null;

            if (!$basePokemonDTO) continue;

            $captured = in_array($pokemonId, $capturedPokemons);
            $shiny = in_array($pokemonId, $shinyCapturedPokemons);

            $capturedPokemonDTO = $capturedPokemonsDTO[$key];
            $capturedPokemonDTO->altCaptured = $capturedPokemonDTO->altCaptured ?: $captured || $shiny;
            $capturedPokemonDTO->altShiny = $capturedPokemonDTO->altShiny ?: $shiny;

            $capturedPokemonsDTO[$key] = $capturedPokemonDTO;

            if ($captured || $shiny) {
                $pokemonDTO = array_filter(
                    $capturedPokemonsDTO[$key]->relatedPokemon,
                    fn(PokemonDTO $p) => $p->pokeId === $pokemonId
                );

                $relatedKey = array_key_first($pokemonDTO);
                $pokemonDTO = new CapturedPokemonDTO($altForm, $captured || $shiny, $shiny);
                $pokemonDTO->captured = $captured || $shiny;
                $pokemonDTO->shiny = $shiny;
                $capturedPokemonsDTO[$key]->relatedPokemon[$relatedKey] = $pokemonDTO;
            }
        }

        usort($capturedPokemonsDTO, fn(CapturedPokemonDTO $a, CapturedPokemonDTO $b) => $a->pokeId <=> $b->pokeId);

        return $capturedPokemonsDTO;
    }

}
