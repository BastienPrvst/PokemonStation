<?php

namespace App\Service;

use App\Entity\CapturedPokemon;
use App\Entity\Items;
use App\Entity\Pokemon;
use App\Entity\User;
use App\Entity\UserItems;
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
    public function calculationOdds(User $user, string $pokeballId): Response
    {
        /*Partie "défaut"*/
        if ($pokeballId === 'default') {
            if ($user->getLaunchs() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }
            $isShiny = $this->isItShiny();
            $rarity = $this->getRarity();
            $user->setLaunchs($user->getLaunchs() - 1);//On retire un lancer à l'utilisateur

            /* @var $pokemons Pokemon[] */
            $pokemons = $this->pokemonRepository->findByRarity($rarity[0]);
            if (empty($pokemons)) {
                do {
                    $rarity = $this->getRarity();
                    $pokemons = $this->pokemonRepository->findByRarity($rarity[0]);
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
        } else {
            /*Code pour les balls alt*/
            $itemsRepo = $this->entityManager->getRepository(Items::class);
            $item = $itemsRepo->findOneBy(['id' => $pokeballId]);
            if (empty($item)) {
                return $this->json([
                    'error' => 'Impossible de récuperer l\'item.'
                ]);
            }

            $userItem = $this->entityManager->getRepository(UserItems::class)
                ->findOneBy(['user' => $user, 'item' => $item]);

            if (empty($userItem)) {
                return $this->json([
                    'error' => 'Vous ne possedez pas cet objet.'
                ]);
            }

            $stats = $item->getStats();
            random_int(1, 1000) / 10 <= $stats['shiny'] ? $isShiny = true : $isShiny = false;
            $customRarity = $stats['rarity'];
            $customType = $stats['type'];

            $rarity = $this->getRarity($customRarity);
            $type = $this->getCustomType($customType);

            $pokemonsFound = $this->pokemonRepository->findByRarityAndType($rarity[0], $type);
            $i = 0;

            if (empty($pokemonsFound)) {
                do {
                    $rarity = $this->getRarity($customRarity);
                    $type = $this->getCustomType($customType);
                    $pokemonsFound = $this->pokemonRepository->findByRarityAndType($rarity[0], $type);
                    $i++;
                } while (empty($pokemonsFound) && $i < 5);

            }

            if (empty($pokemonsFound)) {
                return $this->json([
                    'error' => 'Aucun pokémon trouvé...'
                ]);
            }

            $randomPoke = random_int(0, count($pokemonsFound) - 1);
            $pokemonSpeciesCaptured = $pokemonsFound[$randomPoke];

            /* @var $userItem UserItems */
            $userItem->setQuantity($userItem->getQuantity() - 1);
            if ($userItem->getQuantity() === 0) {
                $this->entityManager->remove($userItem);
            }


            $pokemonCaptured = new CapturedPokemon();
            $pokemonCaptured
                ->setPokemon($pokemonSpeciesCaptured)
                ->setOwner($user)
                ->setCaptureDate(new DateTime())
                ->setShiny($isShiny);
            $pokemon = $pokemonCaptured->getPokemon();
        }

        //Voir si un dresseur a deja vu ce pokémon ou pas

        $alreadyCapturedPokemon = $this->capturedPokemonRepository->findSpeciesCaptured($user);
        $pokemonCapturedId = $pokemon->getPokeId();
        in_array($pokemonCapturedId, $alreadyCapturedPokemon, true) ? $isNew = false : $isNew = true;

        if ($isNew || $isShiny) {
            $pokemonCaptured->setTimesCaptured(1);
            $this->entityManager->persist($pokemonCaptured);
        } else {
            $this->setCoinByRarity($user, $pokemonCaptured);
            $pokemonCaptured->setTimesCaptured($pokemonCaptured->getTimesCaptured() + 1);
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
                'rarity' => $rarity[0],
                'rarityRandom' => ($rarity[1] * 100),
                'new' => $isNew,
            ],
        ]);
    }

    /**
     * @throws RandomException
     */
    private function getRarity(?array $customRarity = null): array
    {
        $randNumber = random_int(0, 10000) / 10000;

        if ($customRarity) {
            $rarities = $customRarity;
        } else {
            $rarities = [
                'C' => 40,
                'PC' => 30,
                'R' => 20,
                'TR' => 8,
                'ME' => 1,
                'GMAX' => 0.4,
                'EX' => 0.3,
                'SR' => 0.2,
                'UR' => 0.1,

            ];
        }

        $totalValue = 0;
        foreach ($rarities as $value) {
            $totalValue += $value;
        }

        $i = 0;
        foreach ($rarities as $rarity => $threshold) {
            $i += $threshold / $totalValue;
            if ($randNumber <= $i) {
                return [$rarity, $randNumber];
            }
        }


        throw new RandomException();
    }

    /**
     * @throws RandomException
     */
    private function getCustomType(array $customType): string
    {
        $totalValue = 0;
        foreach ($customType as $value) {
            $totalValue += $value;
        }

        $randNumber = random_int(0, 10000) / 10000;

        $i = 0;
        foreach ($customType as $type => $threshold) {
            $i += $threshold / $totalValue;
            if ($randNumber <= $i) {
                return $type;
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
            'ME' => 50,
            'GMAX' => 50,
            'SR' => 100,
            'EX' => 100,
            'UR' => 250
        ];

        $capturedRarity = $pokemonCaptured->getPokemon()->getRarity();
        $user->setMoney($user->getMoney() + $rarityScale[$capturedRarity]);
    }
}
