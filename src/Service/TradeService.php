<?php

namespace App\Service;

use App\Entity\CapturedPokemon;
use App\Entity\Trade;
use App\Entity\User;
use App\Enum\TradeStatus;
use App\Enum\TradeUserStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;

class TradeService
{
    /**
     * @param EntityManagerInterface $entityManager
     *
     * Echanges :
     *
     * le joueur 1 voit le profil du joueur et un pokemon qu'il désire
     * Il envoie une demande d'échange
     * Le joueur 2 recoit cette demande,
     * Il peut voir les pokemon en double mini du joueur 1 dans la meme rareté, un pokemon peut l'interesser.
     * Il fait sa proposition
     * Si les deux joueurs sont d'accord, l'echange est conclus et les pokemon echangés
     * Sinon, les utilisateurs peuvent annuler l'echange, (ou marquer un pas interessé)
     *
     * Conditions:
     *
     * Uniquement si les deux usagers ont au minimum un pokemon de la rareté en double.
     * Echanges rareté equivalente sauf shiny
     *
     * Prix pour les deux joueurs :
     *
     * EX 1000, SR 1000, UR 5000
     *
     * Pour les shiny, c'est 2000 pour chaque rareté si ME ou + 8000
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LockFactory $lockFactory,
    ) {
    }


	/**
	 * @param User $user1
	 * @param User $user2
	 * @return Trade
	 */
    public function create(User $user1, User $user2): Trade
    {
		$tradeRepo = $this->entityManager->getRepository(Trade::class);

		$tradeAlreadyExist = $tradeRepo->tradeExist($user1, $user2);
		if ($tradeAlreadyExist !== null) {
			return $tradeAlreadyExist;
		}

        $trade = new Trade();
        $trade
	        ->setUser1($user1)
            ->setUser2($user2)
            ->setStatus(TradeStatus::CREATED)
			->setUser1Status(TradeUserStatus::ONGOING)
	        ->setUser2Status(TradeUserStatus::ONGOING);

        $this->entityManager->persist($trade);
        $this->entityManager->flush();
        return $trade;
    }

    /**
     * @param Trade $trade
     * @param User $user
     * @param CapturedPokemon $pokemon
     * @return array
     */
    public function update(Trade $trade, User $user, CapturedPokemon $pokemon): JsonResponse
    {
        if ($user !== $trade->getUser1() && $user !== $trade->getUser2()) {
            return new JsonResponse(
                ['error' => 'Vous ne faites pas parti de l\'échange.'],
                Response::HTTP_FORBIDDEN,
                [],
                false
            );
        }

        if ($user === $trade->getUser1()) {
            $trade->setPokemonTrade1($pokemon);
        } else {
            $trade->setPokemonTrade2($pokemon);
        }

		$price = $this->calculatePrice($trade);

		return new JsonResponse([
			'price' => $price,
			'trade' => $trade,
		], Response::HTTP_OK, [], false);
    }

    /**
     * @param Trade $trade
     * @param User $user
     * @return Trade|JsonResponse
     */
    public function validate(Trade $trade, User $user): Trade|JsonResponse
    {
        /* @var User $user1 */
        $user1 = $trade->getUser1();
        /* @var User $user2 */
        $user2 = $trade->getUser2();
        /* @var CapturedPokemon $cp1 */
        $cp1 = $trade->getPokemonTrade1();
        /* @var CapturedPokemon $cp2 */
        $cp2 = $trade->getPokemonTrade2();

        if ($user === $user1) {
            $trade->setUser1Status(TradeUserStatus::ACCEPTED);
        } else {
            $trade->setUser2Status(TradeUserStatus::ACCEPTED);
        }

        $price = $this->calculatePrice($trade);

        if ($trade->getUser1Status() === TradeUserStatus::ONGOING || $trade->getUser2Status() === TradeUserStatus::ONGOING) {
            return $trade;
        }

        $poorGuy = null;
        if ($price > $user1->getMoney()) {
            $poorGuy = $user1->getPseudonym();
        } elseif ($price > $user2->getMoney()) {
            $poorGuy = $user2->getPseudonym();
        }

        if ($poorGuy) {
            return new JsonResponse(
                [
                    'erreur' => sprintf('L\'utilisateur %s ne dispose pas d\'assez d\'argent.', $poorGuy),
                ],
                Response::HTTP_FORBIDDEN,
                [],
                false
            );
        }

        // Partie validation de l'échange
        $lock = $this->lockFactory->createLock('trade' . $trade->getId());

        try {
            $capturedPokemonRepo = $this->entityManager->getRepository(CapturedPokemon::class);

            $cp1->setTimesCaptured($cp1->getTimesCaptured() - 1);
            $cp1AlreadyExist = $capturedPokemonRepo->findOneBy(
                [
                    'owner'   => $user2,
                    'pokemon' => $cp1->getPokemon(),
                ]
            );
            $cp2->setTimesCaptured($cp2->getTimesCaptured() - 1);
            $cp2AlreadyExist = $capturedPokemonRepo->findOneBy(
                [
                    'owner'   => $user1,
                    'pokemon' => $cp2->getPokemon(),
                ]
            );

            $user1->setMoney($user1->getMoney() - $price);
            $user2->setMoney($user2->getMoney() - $price);

            if (!$cp1AlreadyExist) {
                $poke = new CapturedPokemon();
                $poke
                    ->setOwner($user2)
                    ->setPokemon($cp1->getPokemon())
                    ->setCaptureDate(new \DateTime())
                    ->setShiny($cp1->getShiny());

                $this->entityManager->persist($poke);
            } else {
                $cp1AlreadyExist->setTimesCaptured($cp1AlreadyExist->getTimesCaptured() + 1);
            }

            if (!$cp2AlreadyExist) {
                $poke = new CapturedPokemon();
                $poke
                    ->setOwner($user1)
                    ->setPokemon($cp2->getPokemon())
                    ->setCaptureDate(new \DateTime())
                    ->setShiny($cp2->getShiny());

                $this->entityManager->persist($poke);
            } else {
                $cp2AlreadyExist->setTimesCaptured($cp2AlreadyExist->getTimesCaptured() + 1);
            }

            $this->entityManager->flush();
            $trade->setStatus(TradeStatus::COMPLETED);
        } catch (\Exception $exception) {
            return new JsonResponse(
                ['error' => 'Une erreur s\'est produite pendant la validation de l\'échange.'],
                500,
                [],
                false
            );
        }

        $lock->release();
        return $trade;
    }


    public function cancel(Trade $trade): void
    {
        $trade->setStatus(TradeStatus::CANCELLED);
        $this->entityManager->flush();
    }

    /**
     * @param Trade $trade
     * @return int
     */
	private function calculatePrice(Trade $trade): int
	{
		$prices = [
			'C'    => 100,
			'PC'   => 200,
			'R'    => 500,
			'TR'   => 800,
			'GMAX' => 1500,
			'ME'   => 1500,
			'EX'   => 2500,
			'SR'   => 3000,
			'UR'   => 5000,
		];

		$rarity1 = $trade->getTradePoke1()?->getRarity() ?? '';
		$rarity2 = $trade->getTradePoke2()?->getRarity() ?? '';

		$price1 = $prices[$rarity1] ?? 0;
		$price2 = $prices[$rarity2] ?? 0;

		return max($price1, $price2);
	}
}
