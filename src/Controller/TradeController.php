<?php

namespace App\Controller;

use App\Entity\CapturedPokemon;
use App\Entity\Trade;
use App\Entity\User;
use App\Repository\CapturedPokemonRepository;
use App\Repository\TradeRepository;
use App\Repository\UserRepository;
use App\Service\TradeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

final class TradeController extends AbstractController
{
	public function __construct(
		private readonly TradeService $tradeService,
		private readonly CapturedPokemonRepository $cpRepository,
	) {
	}

	#[Route(path: '/trades/', name: 'app_trades')]
	#[IsGranted('ROLE_SUPER_ADMIN')]
	public function allUserTrades(TradeRepository $tradeRepository, UserRepository $userRepository): Response
    {
	    /** @var User $user */
	    $user = $this->getUser();
		$userTrades = $tradeRepository->findByUser($user);
		$friends = $user->getFriendships();
		$lastConnectedPpl = $userRepository->findLastConnected();
		return $this->render('main/allTrades.html.twig', [
			'trades' => $userTrades,
			'friends' => $friends,
			'lastConnectedPpl' => $lastConnectedPpl,
		]);
	}


	#[Route(path: '/trade/{id}', name: 'app_trade_create')]
	#[IsGranted('ROLE_SUPER_ADMIN')]
	public function createTrade(User $user): Response
	{
		/* @var User $connectedUser */
		$connectedUser = $this->getUser();
		if ($user === $connectedUser) {
			return new Response('Vous ne pouvez pas faire d\'échange avec vous même.', Response::HTTP_BAD_REQUEST);
		}

		$trade = $this->tradeService->create($connectedUser, $user);
		$rarityScale = [
			'UR' => 1,
			'EX' => 2,
			'SR' => 3,
			'GMAX' => 4,
			'ME' => 5,
			'TR' => 6,
			'R' => 7,
			'PC' => 8,
			'C' => 9,
		];

		$availableUser1 = $this->cpRepository->findTradeable($connectedUser);

		usort($availableUser1, static function ($a, $b) use ($rarityScale) {
			return $rarityScale[$a->getPokemon()->getRarity()] <=> $rarityScale[$b->getPokemon()->getRarity()];
		});

		//Touuut ce que l'utilisateur 2 a de disponible a l'echange
		$availableUser2 = $this->cpRepository->findTradeable($user);
		//Tout ce qui peut interesser l'utilisateur 1 dans ce qu'a le 2
		$interesstingUser2 = $this->cpRepository->findInteressting($user, $connectedUser);

		$availableIds = array_map(fn($cp) => $cp->getId(), $interesstingUser2);
		foreach ($availableUser2 as $cp) {
			if (!in_array($cp->getId(), $availableIds, true)) {
				/** @var CapturedPokemon $cp */
				$cp->setInPossession(true);
			}
		}

		usort($availableUser2, static function ($a, $b) use ($rarityScale) {
			return $rarityScale[$a->getPokemon()->getRarity()] <=> $rarityScale[$b->getPokemon()->getRarity()];
		});

		return $this->render('main/trade.html.twig', [
			'user' => $user,
			'trade' => $trade,
			'pokeAvailable1' => $availableUser1,
			'pokeAvailable2' => $availableUser2,
		]);
	}

	/**
	 * @throws ExceptionInterface
	 */
	#[Route(path: '/trade/update/{trade}', name: 'app_trade_update')]
	#[IsGranted('ROLE_SUPER_ADMIN')]
	public function updateTrade(Trade $trade, Request $request): JsonResponse
	{
		$pokeId = $request->request->get('pokemonId');

		/* @var User $user */
		$user = $this->getUser();

		/* @var CapturedPokemon $capturedPokemon */
		$capturedPokemon = $this->cpRepository->find($pokeId);
		if (
            !$capturedPokemon ||
            $capturedPokemon->getOwner() !== $user
        ) {
			return new JsonResponse([
				'error' => 'Impossible de valider ce Pokémon',
			], Response::HTTP_BAD_REQUEST, [], false);
		}

		if (
			$capturedPokemon->getShiny() === false && $capturedPokemon->getQuantity() <= 1
		) {
			return new JsonResponse([
				'error' => 'Quantité du Pokémon insuffisant.',
			], Response::HTTP_BAD_REQUEST, [], false);
		}
		return $this->tradeService->update($trade, $user, $capturedPokemon);
	}

	#[Route(path: '/trade/validate/{trade}', name: 'app_trade_validate')]
	#[IsGranted('ROLE_SUPER_ADMIN')]
	public function finalizeTrade(Trade $trade): null|JsonResponse
	{
		/* @var User $user */
		$user = $this->getUser();
		return $this->tradeService->validate($trade, $user);
	}

	#[Route(path: '/trade/cancel/{trade}', name: 'app_trade_cancel')]
	#[IsGranted('ROLE_SUPER_ADMIN')]
	public function cancelTrade(Trade $trade): Response
	{
		$this->tradeService->cancel($trade);
		return $this->redirectToRoute('app_trades');
	}

}
