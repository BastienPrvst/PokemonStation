<?php

namespace App\Service;

use App\Entity\CapturedPokemon;
use App\Entity\Pokemon;
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
        private readonly PokemonRepository $pokemonRepository,
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

        if ($user->getLaunchs() < 1) {
            return $this->json([
                'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
            ]);
        }

        $rarity = $this->getCommonRarity($randomRarity);
        $isShiny = $this->isItShiny();
        $user->setLaunchs($user->getLaunchs() - 1);//On retire un lancer à l'utilisateur

        /* @var $pokemons Pokemon[] */
        $pokemons = $this->pokemonRepository->findByRarity($rarity);
        if (empty($pokemons)) {
            do {
                $randomRarity = random_int(10, 1000) / 10;
                $rarity = $this->getCommonRarity($randomRarity);
                $pokemons = $this->pokemonRepository->findByRarity($rarity);
            } while (empty($pokemons));
        }
        $randomPoke = random_int(0, count($pokemons) - 1);
        $pokemonSpeciesCaptured = $pokemons[$randomPoke];
        $pokemonCaptured = new CapturedPokemon();
        $pokemonCaptured
            ->setPokemon($pokemonSpeciesCaptured)
            ->setOwner($user)
            ->setCaptureDate(new DateTime())
            ->setShiny($isShiny);

        /* @var Pokemon $pokemon*/
        $pokemon = $pokemonCaptured->getPokemon();

        //Voir si un dresseur a deja vu ce pokémon ou pas
        $alreadyCapturedPokemon = $this->capturedPokemonRepository->findSpeciesCaptured($user);
        $pokemonCapturedId = $pokemon->getPokeId();
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
                'id' => $pokemon->getId(),
                'name' => $pokemon->getName(),
                'type' => $pokemon->getType(),
                'type2' => $pokemon->getType2(),
                'description' => $pokemon->getDescription(),
                'nameEN' => $pokemon->getNameEN(),
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
            99.2 => 'GMAX',
            99.7 => 'EX',
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
        random_int(1, 200) === 1 ? $isShiny = true : $isShiny = false;
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
            'GMAX' => 50,
            'SR' => 100,
            'EX' => 100,
            'UR' => 250
        ];

        $capturedRarity = $pokemonCaptured->getPokemon()->getRarity();
        $user->setMoney($user->getMoney() + $rarityScale[$capturedRarity]);
    }
}
