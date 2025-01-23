<?php

namespace App\Service;

use App\Entity\CapturedPokemon;
use App\Entity\User;
use App\Repository\CapturedPokemonRepository;
use App\Repository\PokemonRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PokemonOdds extends AbstractController
{
    public function __construct(
        private readonly PokemonRepository      $pokemonRepository,
        private readonly CapturedPokemonRepository $capturedPokemonRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function calculationOdds(User $user, int $pokeballId): Response
    {
        $randomRarity = random_int(10, 1000) / 10;
        if ($pokeballId === 1) { //LANCERS NORMAUX
            if ($user->getLaunchs() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }
            $rarity = $this->getCommonRarity($randomRarity);
            $isShiny = $this->isItShiny();
            $user->setLaunchs($user->getLaunchs() - 1);//On retire un lancer à l'utilisateur
        } elseif ($pokeballId === 2) { //HYPER BALL
            if ($user->getHyperBall() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }

            if ($randomRarity <= 70) {
                $rarity = 'TR'; //70%
            } elseif ($randomRarity <= 95) {
                $rarity = 'ME'; //25%
            } else {
                $rarity = 'SR';
            }
            $isShiny = $this->isItShiny();
            $user->setHyperBall($user->getHyperBall() - 1); //On retire un lancer à l'utilisateur
        } elseif ($pokeballId === 3) { //SHINY BALL
            if ($user->getShinyBall() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }
            $rarity = $this->getCommonRarity($randomRarity);
            $isShiny = true;
            $user->setShinyBall($user->getShinyBall() - 1);
        } elseif ($pokeballId === 4) { //MASTER BALL
            if ($user->getMasterBall() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }
            $randomRarity <= 80 ? $rarity = 'EX' : $rarity = 'UR';
            $isShiny = $this->isItShiny();
            $user->setMasterBall($user->getMasterBall() - 1);
        } else {
            return $this->json([
                'error' => 'Lancer invalide.',
                'bug' => $pokeballId,
            ]);
        }

        $pokemons = $this->pokemonRepository->findByRarity($rarity);
        $randomPoke = random_int(0, count($pokemons) - 1);
        $pokemonSpeciesCaptured = $pokemons[$randomPoke];
        $pokemonCaptured = new CapturedPokemon();
        $pokemonCaptured
            ->setPokemon($pokemonSpeciesCaptured)
            ->setOwner($user)
            ->setCaptureDate(new DateTime())
            ->setShiny($isShiny);

        //Voir si un dresseur a deja vu ce pokémon ou pas
        $alreadyCapturedPokemon = $this->capturedPokemonRepository->findSpeciesCaptured($user);
        $pokemonCapturedId = $pokemonCaptured->getPokemon()->getPokeId();
        in_array($pokemonCapturedId, $alreadyCapturedPokemon, true) ? $isNew = false : $isNew = true;
        if ($isNew || $isShiny) {
            $this->entityManager->persist($pokemonCaptured);
        } else {
            $this->setCoinByRarity($user, $pokemonCaptured);
        }
        $user->setLaunchCount($user->getLaunchCount() + 1);
        $this->entityManager->flush();

        return $this->json([
            'captured_pokemon' => [
                'id' => $pokemonCaptured->getPokemon()->getId(),
                'name' => $pokemonCaptured->getPokemon()->getName(),
                'gif' => $pokemonCaptured->getPokemon()->getGif(),
                'type' => $pokemonCaptured->getPokemon()->getType(),
                'type2' => $pokemonCaptured->getPokemon()->getType2(),
                'description' => $pokemonCaptured->getPokemon()->getDescription(),
                'shiny' => $pokemonCaptured->getShiny(),
                'rarity' => $rarity,
                'rarityRandom' => $randomRarity,
                'new' => $isNew,
            ],
        ]);
    }

    /**
     * @throws RandomException
     */
    private function getCommonRarity(float|int $randomRarity): string
    {
        $rarities = [
            40 => 'C',
            70 => 'PC',
            90 => 'R',
            98 => 'TR',
            99 => 'ME',
            99.5 => 'EX',
            99.9 => 'SR',
            100 => 'UR',
        ];

        foreach ($rarities as $threshold => $rarity) {
            if ($randomRarity <= $threshold) {
                return $rarity;
            }
        }

        throw new RandomException();
    }

    /**
     * @throws RandomException
     */
    private function isItShiny(): bool
    {
        rand(1, 200) === 1 ? $isShiny = true : $isShiny = false;
        return $isShiny;
    }

    private function setCoinByRarity(User $user, CapturedPokemon $pokemonCaptured): void
    {
        //Valeur en pièce si le Pokémon à déja été vu
        $rarityScale = [
            'C' => 1,
            'PC' => 3,
            'R' => 5,
            'TR' => 10,
            'ME' => 25,
            'SR' => 50,
            'EX' => 50,
            'UR' => 250
        ];

        $capturedRarity = $pokemonCaptured->getPokemon()->getRarity();
        $user->setMoney($user->getMoney() + $rarityScale[$capturedRarity]);
    }
}
