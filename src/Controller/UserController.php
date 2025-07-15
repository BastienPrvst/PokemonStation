<?php

namespace App\Controller;

use App\Entity\CapturedPokemon;
use App\Entity\Trade;
use App\Entity\User;
use App\Form\EditModifyProfilFormType;
use App\Repository\CapturedPokemonRepository;
use App\Repository\PokemonRepository;
use App\Service\TradeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    public function __construct(
        private readonly PokemonRepository $pokemonRepository,
		private readonly CapturedPokemonRepository $cpRepository,
        private readonly EntityManagerInterface $entityManager,
		private readonly TradeService $tradeService,
    ) {
    }

    #[Route('/mon-profil', name: 'app_profil')]
    #[IsGranted('ROLE_USER')]
    public function profil(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $allAvatars = str_replace('.gif', '', $this->getAllAvatars());

        if ($user->getAvatar() === null || !in_array(trim($user->getAvatar()), $allAvatars, true)) {
            $user->setAvatar('trainer1');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $this->render(
            'main/profile.html.twig',
            array_merge(
                $this->prepareUserInfo($user),
                ['avatars' => $allAvatars]
            )
        );
    }

    #[Route('/modifier-mon-profil', name: 'app_profil-modify')]
    #[IsGranted('ROLE_USER')]
    public function modifyProfil(
        UserPasswordHasherInterface $encoder,
        Request $request,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(EditModifyProfilFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // vérifier si le mot de passe actuel est correct
            $currentPassword = $form->get('currentPassword')->getData();

            if (!$encoder->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Mauvais mot de passe !'));
            } else {
                // Modification du profil
                $newPassword = $form->get('plainPassword')->getData();
                if ($newPassword !== null) {
                    $hashNewPassword = $encoder->hashPassword($user, $newPassword);
                    $user->setPassword($hashNewPassword);
                }

                $this->entityManager->flush();

                // Message flash de succès
                $this->addFlash('success', 'Votre profil a été modifié avec succès');

                return $this->redirectToRoute('app_profil');
            }
        }

        return $this->render('main/modify_profile.html.twig', [
            'editModifyProfilForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/profil/{pseudonym}', name: 'app_user_showprofile')]
    #[IsGranted('ROLE_USER')]
    public function showProfile(User $user): Response
    {
        if ($user->getAvatar() === null) {
            $user->setAvatar('trainer1');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $allGens = $this->pokemonRepository->pokemonSeenByGen($user);
        $allTrueGens = $this->pokemonRepository->pokemonSeenByGenTrue($user);

        for ($i = 0, $iMax = count($allGens); $i < $iMax; $i++) {
            $allGens[$i]['true_gen_captured'] = $allTrueGens[$i]['true_gen_captured'];
        }

        return $this->render(
            'main/show_profile.html.twig',
            [...$this->prepareUserInfo($user)]
        );
    }

    private function prepareUserInfo(User $user): array
    {

        $rarityScale = [
            'C' => 1,
            'PC' => 2,
            'R' => 3,
            'TR' => 4,
            'ME' => 5,
            'GMAX' => 6,
            'EX' => 7,
            'SR' => 8,
            'UR' => 9
        ];

        $captureRepo = $this->entityManager->getRepository(CapturedPokemon::class);
        $rarityArray = $captureRepo->countUserCapturedPokemon($user);
        $shinyStats = $captureRepo->countUserShinies($user);
        $allGens = $this->pokemonRepository->getAllGenDex($user);
        $pokedexUser = $captureRepo->countDistinctUserCapturedPokemon($user);

        usort($rarityArray, static function ($a, $b) use ($rarityScale) {
            return $rarityScale[$a['rarity']] <=> $rarityScale[$b['rarity']];
        });

        return [
            'pokedexSize' => $this->pokemonRepository->getFullPokedexSize(),
            'pokedexUser' => $pokedexUser,
            'user' => $user,
            'rarityStats' => $rarityArray,
            'shinyStats' => $shinyStats,
            'allGens' => $allGens,
        ];
    }

    private function getAllAvatars(): array
    {
        /* @var $user User */
        $user = $this->getUser();
        $dirPath = dirname(__DIR__, 2) . "/public/medias/images/trainers";
        $files = scandir($dirPath, SCANDIR_SORT_ASCENDING);
        $realFiles = [];
        foreach ($files as $file) {
            if (!is_file($dirPath . '/' . $file)) {
                continue;
            }

            if (
                $file === 'Spirit.gif' &&
                !($user->getPseudonym() === 'Spirit' &&
                    in_array(
                        'ROLE_ADMIN',
                        $user->getRoles(),
                        true
                    ))
            ) {
                continue;
            }

            $realFiles[] = $file;
        }
        return $realFiles;
    }

    #[Route(path: '/trade/{id}', name: 'app_trade_create')]
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

	#[Route(path: '/trade/update/{trade}', name: 'app_trade_update')]
	public function updateTrade(Trade $trade, Request $request): JsonResponse
	{
		$pokemonId = $request->request->get('pokemonId');
		/* @var CapturedPokemon $capturedPokemon */
		$capturedPokemon = $this->cpRepository->find($pokemonId);
		if (
            !$capturedPokemon ||
			$capturedPokemon->getQuantity() <= 1 || $capturedPokemon->getOwner() !== $this->getUser()
        ) {
			return new JsonResponse([
				'error' => 'Impossible de valider ce Pokémon',
			], Response::HTTP_BAD_REQUEST, [], false);
		}

		/* @var User $user */
		$user = $this->getUser();
		$tradeService = $this->tradeService;
		$result = $tradeService->update($trade, $user, $capturedPokemon);

		return new JsonResponse([
			'result' => $result,
		], Response::HTTP_OK, [], true);
	}
}
