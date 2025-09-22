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
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

readonly class TradeService
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
     *
     * Prix pour les deux joueurs :
     *
     * EX 1000, SR 1000, UR 5000
     *
     * Pour les shiny, c'est 2000 pour chaque rareté si ME ou + 8000
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LockFactory $lockFactory,
	    private SerializerInterface $serializer,
    ) {
    }


	/**
	 * @param User $user1
	 * @param User $user2
	 * @return Trade
	 */
    public function create(User $user1, User $user2): Trade
    {
	    $lock = $this->lockFactory->createLock(
		    'trade_lock_' . $user1->getId() . '_' . $user2->getId()
	    );

	    if (!$lock->acquire(true)) {
		    throw new \RuntimeException('Impossible de créer un trade, verrou non obtenu.');
	    }

	    try {
		    $tradeRepo = $this->entityManager->getRepository(Trade::class);

		    $tradeAlreadyExist = $tradeRepo->tradeExist($user1, $user2);

		    if (
			    $tradeAlreadyExist !== null &&
			    in_array($tradeAlreadyExist->getStatus(), [TradeStatus::CREATED, TradeStatus::ONGOING], true)
		    ) {
			    return $tradeAlreadyExist;
		    }

		    $trade = (new Trade())
			    ->setUser1($user1)
			    ->setUser2($user2)
			    ->setStatus(TradeStatus::CREATED)
			    ->setUser1Status(TradeUserStatus::ONGOING)
			    ->setUser2Status(TradeUserStatus::ONGOING)
			    ->setPrice(0);

		    $this->entityManager->persist($trade);
		    $this->entityManager->flush();

		    return $trade;
	    } finally {
		    $lock->release();
	    }
    }

	/**
	 * @param Trade $trade
	 * @param User $user
	 * @param CapturedPokemon $pokemon
	 * @return JsonResponse
	 * @throws ExceptionInterface
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

		$trade->setStatus(TradeStatus::ONGOING);

	    $lock = $this->lockFactory->createLock('TradeValidateLock_' . $trade->getId());

	    if (!$lock->acquire()) {
		    return new JsonResponse(['error' => 'Echange déjà en cours de traitement'], 423);
	    }

	    try {
			if ($user === $trade->getUser1()) {
                $trade->setPokemonTrade1($pokemon);
                $trade->setUser1Status(TradeUserStatus::VALIDATED);
				if ($trade->getUser2Status() === TradeUserStatus::ACCEPTED) {
					$trade->setUser2Status(TradeUserStatus::VALIDATED);
				}
		    } else {
			    $trade->setPokemonTrade2($pokemon);
			    $trade->setUser2Status(TradeUserStatus::VALIDATED);
				if ($trade->getUser1Status() === TradeUserStatus::ACCEPTED) {
					$trade->setUser1Status(TradeUserStatus::VALIDATED);
				}
		    }
		    $price = $this->calculatePrice($trade);
		    $trade->setPrice($price);
	    } finally {
		    $lock->release();
	    }

		$this->entityManager->persist($trade);
		$this->entityManager->flush();
	    $data = [
		    'price' => $price,
		    'trade' => $trade,
	    ];

	    $json = $this->serializer->serialize($data, 'json', ['groups' => 'getTrade']);

	    return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

	/**
	 * @param Trade $trade
	 * @param User $user
	 * @return JsonResponse
	 */
    public function validate(Trade $trade, User $user): JsonResponse
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

		$this->entityManager->flush();

        $price = $this->calculatePrice($trade);

        if (
			$trade->getUser1Status() !== TradeUserStatus::ACCEPTED ||
			$trade->getUser2Status() !== TradeUserStatus::ACCEPTED
        ) {
            return new JsonResponse(
	            ['info' => 'En attente de validation du second Pokémon.',
				'validate' => false],
                Response::HTTP_OK,
                [],
                false
            );
        }
	    $lock = $this->lockFactory->createLock('trade' . $trade->getId());

        $poorGuy = null;
        if ($price > $user1->getMoney()) {
            $poorGuy = $user1->getPseudonym();
        } elseif ($price > $user2->getMoney()) {
            $poorGuy = $user2->getPseudonym();
        }

        if ($poorGuy) {
            return new JsonResponse(
                [
                    'info' => sprintf('L\'utilisateur %s ne dispose pas d\'assez d\'argent.', $poorGuy),
	                'validate' => false
                ],
                Response::HTTP_OK,
                [],
                false
            );
        }

        // Partie validation de l'échange

	    foreach ([$cp1, $cp2] as $cp) {
		    $required = $cp->getShiny() === true ? 1 : 2;

		    if ($cp->getQuantity() < $required) {
			    return new JsonResponse(
				    [
					    'info' => sprintf(
						    "L'utilisateur %s ne dispose pas du Pokémon %s en quantité suffisante",
						    $cp->getOwner()?->getPseudonym() ?? 'inconnu',
						    $cp->getPokemon()?->getName() ?? 'inconnu'
					    ),
					    'validate' => false
				    ],
				    Response::HTTP_OK,
				    [],
				    false
			    );
		    }
	    }

        try {
            $capturedPokemonRepo = $this->entityManager->getRepository(CapturedPokemon::class);

            $cp1->setQuantity($cp1->getQuantity() - 1);
            $cp1AlreadyExist = $capturedPokemonRepo->findOneBy(
                [
                    'owner'   => $user2,
                    'pokemon' => $cp1->getPokemon(),
                ]
            );
            $cp2->setQuantity($cp2->getQuantity() - 1);
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
	                ->setTimesCaptured(0)
	                ->setQuantity(1)
                    ->setShiny($cp1->getShiny());

                $this->entityManager->persist($poke);
            } else {
                $cp1AlreadyExist->setQuantity(($cp1AlreadyExist->getQuantity() + 1));
            }

            if (!$cp2AlreadyExist) {
                $poke = new CapturedPokemon();
                $poke
                    ->setOwner($user1)
                    ->setPokemon($cp2->getPokemon())
                    ->setCaptureDate(new \DateTime())
	                ->setTimesCaptured(0)
	                ->setQuantity(1)
                    ->setShiny($cp2->getShiny());

                $this->entityManager->persist($poke);
            } else {
                $cp2AlreadyExist->setQuantity(($cp2AlreadyExist->getQuantity() + 1));
            }

            $trade->setStatus(TradeStatus::COMPLETED);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            return new JsonResponse(
                ['error' => $exception->getMessage(),
                'validate' => false
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [],
                false
            );
        }

        $lock->release();

		return new JsonResponse(['validate' => true], Response::HTTP_OK, [], false);
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

		$rarity1 = $trade->getPokemonTrade1()?->getPokemon()?->getRarity() ?? '';
		$rarity2 = $trade->getPokemonTrade2()?->getPokemon()?->getRarity() ?? '';

		$price1 = $prices[$rarity1] ?? 0;
		$price2 = $prices[$rarity2] ?? 0;

		$price1 *= ($trade->getPokemonTrade1()?->getShiny() ? 5 : 1);
		$price2 *= ($trade->getPokemonTrade2()?->getShiny() ? 5 : 1);

		return max($price1, $price2);
	}
}
