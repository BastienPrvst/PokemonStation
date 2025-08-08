<?php

namespace App\Controller;

use App\Entity\CapturedPokemon;
use App\Entity\Trade;
use App\Entity\User;
use App\Repository\CapturedPokemonRepository;
use App\Service\TradeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TradeController extends AbstractController
{
	public function __construct(
		private readonly TradeService $tradeService,
		private readonly CapturedPokemonRepository $cpRepository,
	) {
	}

	#[\Symfony\Component\Routing\Annotation\Route(path: '/trade/{id}', name: 'app_trade_create')]
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

		$availableUser2 = $this->cpRepository->findInteressting($user, $connectedUser);

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

	#[Route(path: '/trade/update/{trade}', name: 'app_trade_update', methods: ['POST'])]
	public function updateTrade(Trade $trade, Request $request): JsonResponse
	{
		$pokeId = $request->request->get('pokemonId');

		/* @var User $user */
		$user = $this->getUser();

		/* @var CapturedPokemon $capturedPokemon */
		$capturedPokemon = $this->cpRepository->find($pokeId);
		if (
			!$capturedPokemon ||
			$capturedPokemon->getQuantity() <= 1 || $capturedPokemon->getOwner() !== $user
		) {
			return new JsonResponse([
				'error' => 'Impossible de valider ce Pokémon',
			], Response::HTTP_BAD_REQUEST, [], false);
		}
		$tradeService = $this->tradeService;
		$result = $tradeService->update($trade, $user, $capturedPokemon);

		return new JsonResponse(
			$result,
            Response::HTTP_OK,
            [],
            true
        );
	}
}
