<?php

namespace App\Service;

use App\Entity\CapturedPokemon;
use App\Entity\Items;
use App\Entity\Pokemon;
use App\Entity\User;
use App\Entity\UserItems;
use App\Repository\CapturedPokemonRepository;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PokemonOddsService extends AbstractController
{
    private array $rarityScale = [
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

    public function __construct(
        private readonly PokemonRepository $pokemonRepository,
        private readonly CapturedPokemonRepository $capturedPokemonRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DiscordWebHookService $discordWebHookService
    ) {
    }

    /**
     * @throws RandomException
     * @throws TransportExceptionInterface
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
            $user->setLaunchs($user->getLaunchs() - 1);//On retire un lancer à l'utilisateur
            $this->entityManager->flush();
            $isShiny = $this->isItShiny();

            do {
                $rarity = $this->getRarity();
                $pokemons = $this->pokemonRepository->findByRarity($rarity[0]);
            } while (empty($pokemons));

            $randomPoke = array_rand($pokemons);
            $pokemonSpeciesCaptured = $pokemons[$randomPoke];
            $capturedPokemon = new CapturedPokemon();
            $capturedPokemon
                ->setPokemon($pokemonSpeciesCaptured)
                ->setOwner($user)
                ->setCaptureDate(new \DateTime('', new \DateTimeZone('Europe/Paris')))
                ->setShiny($isShiny);

            /* @var Pokemon $pokemon*/
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

            do {
                $rarity = $this->getRarity($customRarity);
                $type = $this->getCustomType($customType);
                $pokemonsFound = $this->pokemonRepository->findByRarityAndType($rarity[0], $type);
            } while (empty($pokemonsFound));

            $randomPoke = array_rand($pokemonsFound);
            $pokemonSpeciesCaptured = $pokemonsFound[$randomPoke];

            /* @var $userItem UserItems */
            $userItem->setQuantity($userItem->getQuantity() - 1);
            if ($userItem->getQuantity() === 0) {
                $this->entityManager->remove($userItem);
            }

            $capturedPokemon = new CapturedPokemon();
            $capturedPokemon
                ->setPokemon($pokemonSpeciesCaptured)
                ->setOwner($user)
                ->setCaptureDate(new \DateTime('', new \DateTimeZone('Europe/Paris')))
                ->setShiny($isShiny);
        }

        $pokemonCapturedPokeId = $pokemonSpeciesCaptured->getPokeId();

        if ($isShiny) {
            $capturedPokeIds = $this->capturedPokemonRepository->findShinyCaptured($user);
            $firstTimeShiny = !in_array($pokemonCapturedPokeId, $capturedPokeIds, true);
            $firstTimeNonShiny = false;
        } else {
            $capturedPokeIds = $this->capturedPokemonRepository->findSpeciesCaptured($user);
            $firstTimeNonShiny = !in_array($pokemonCapturedPokeId, $capturedPokeIds, true);
            $firstTimeShiny = false;
        }

        if ($firstTimeNonShiny || $firstTimeShiny) {
            $capturedPokemon->setTimesCaptured(1);
			$capturedPokemon->setQuantity(1);
            $this->entityManager->persist($capturedPokemon);
            $isNew = true;
            $cpDiscord = $capturedPokemon;
        } else {
            $this->setCoinByRarity($user, $capturedPokemon, $isShiny);

            if ($isShiny) {
                $this->entityManager->persist($capturedPokemon);
                $capturedPokemon->setTimesCaptured(-1);
				$capturedPokemon->setQuantity($capturedPokemon->getQuantity() + 1);
                //Permet un comptage par mois du top shiny mais ne trouble pas le nombre de fois qu'il est capturé
            }

            /* @var $alreadyCapturedPokemon CapturedPokemon */
            $alreadyCapturedPokemon = $this->capturedPokemonRepository->findOnePokemon(
                $user,
                (bool)$isShiny,
                $pokemonSpeciesCaptured
            );
            $alreadyCapturedPokemon->setTimesCaptured($alreadyCapturedPokemon->getTimesCaptured() + 1);
	        $capturedPokemon->setQuantity($capturedPokemon->getQuantity() + 1);
            $alreadyCapturedPokemon->setCaptureDate(new \DateTime('', new \DateTimeZone('Europe/Paris')));
            $cpDiscord = $alreadyCapturedPokemon;
            $isNew = false;
            //Multiplication des pièces par nombre de fois capturés.
            $multiply = $this->multiplyCoins($alreadyCapturedPokemon, $isShiny, $user);
        }

        $user->setLaunchCount($user->getLaunchCount() + 1);

        //Gestion du score

        $multiplier = $isShiny ? 10 : 1;
        $scoreToAdd = $this->rarityScale[$rarity[0]] * $multiplier;
        $user->setScore($user->getScore() + $scoreToAdd);

        $this->entityManager->flush();

        //Partie discord
        if ($_ENV['APP_ENV'] === 'prod') {
            $discordError = $this->discordWebHookService->sendToDiscordWebHook(
                $user,
                $cpDiscord,
                $firstTimeShiny,
                $firstTimeNonShiny
            );
        }

        return $this->json([
            'captured_pokemon' => [
                'id' => $pokemonSpeciesCaptured->getId(),
                'name' => $pokemonSpeciesCaptured->getName(),
                'type' => $pokemonSpeciesCaptured->getType(),
                'type2' => $pokemonSpeciesCaptured->getType2(),
                'description' => $pokemonSpeciesCaptured->getDescription(),
                'nameEN' => $pokemonSpeciesCaptured->getNameEN(),
                'shiny' => $capturedPokemon->getShiny(),
                'rarity' => $rarity[0],
                'rarityRandom' => ($rarity[1] * 100),
                'new' => $isNew,
                'discordError' => $discordError ?? null,
                'multiplyMoney' => $multiply ?? 0,
                'times_captured' => $cpDiscord->getTimesCaptured(),
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
                'TR' => 7,
                'ME' => 1,
                'GMAX' => 1,
                'SR' => 0.7,
                'EX' => 0.3,
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

    private function setCoinByRarity(User $user, CapturedPokemon $pokemonCaptured, bool $shiny): void
    {
        //Valeur en pièce si le Pokémon à déja été vu


        $capturedRarity = $pokemonCaptured->getPokemon()->getRarity();
        $numberToAdd = $this->rarityScale[$capturedRarity];
        if ($shiny === true) {
            $numberToAdd *= 10;
        }

        $user->setMoney($user->getMoney() + $numberToAdd);
    }

    /**
     * @param CapturedPokemon $pokemonCaptured
     * @param bool $shiny
     * @param User $user
     * @return false|string
     */
    private function multiplyCoins(CapturedPokemon $pokemonCaptured, bool $shiny, User $user): int
    {
        $multipliers = [
            5 => 5,
            10 => 10,
            20 => 20,
            50 => 50,
            100 => 100,
            1000 => 1000
        ];

        $capturedRarity = $pokemonCaptured->getPokemon()->getRarity();

        $timeCaptured = $pokemonCaptured->getTimesCaptured();
        in_array($timeCaptured, $multipliers, true) ?
            $moneyToAdd = ($this->rarityScale[$capturedRarity] * $multipliers[$timeCaptured]) :
            $moneyToAdd = 0;

        if ($shiny === true) {
            $moneyToAdd *= 10;
        }

        if ($moneyToAdd > 0) {
            $user->setMoney($user->getMoney() + $moneyToAdd);
            return $moneyToAdd;
        }
        return 0;
    }
}
