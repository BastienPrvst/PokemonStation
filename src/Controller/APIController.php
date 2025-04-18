<?php

namespace App\Controller;

use App\DTO\PokemonDTO;
use App\Entity\Generation;
use App\Entity\Items;
use App\Entity\Pokemon;
use App\Entity\User;
use App\Entity\UserItems;
use App\Repository\PokemonRepository;
use App\Service\CapturedPokemonService;
use App\Service\PokemonOddsService;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class APIController extends AbstractController
{
    public function __construct(
        private readonly PokemonOddsService $pokemonOdds,
        private readonly CapturedPokemonService $capturedPokemonService,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws RandomException
     */
    #[Route('/capture-api', name: 'app_capture_api')]
    #[IsGranted('ROLE_USER')]
    public function captureApi(Request $request): Response
    {
        $pokeballId = $request->get('pokeballData');
        /** @var User $user * */
        $user = $this->getUser();
        return $this->pokemonOdds->calculationOdds($user, $pokeballId);
    }

    #[Route('/pokedex-api/{pokeId}', name: 'app_pokedex_api')]
    #[IsGranted('ROLE_USER')]
    public function pokedexApi(Pokemon $pokemon): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $baseForm = $pokemon->getRelateTo();

        if ($baseForm) {
            $pokemons = [$baseForm, ...$baseForm->getRelatedPokemon()];
        } else {
            $pokemons = [$pokemon, ...$pokemon->getRelatedPokemon()];
        }

        $captured = $this->capturedPokemonService->userCapturedByGeneration($user, $pokemons);

        if ($baseForm) {

            $baseFormDTO = $captured[0];
            $target = array_filter(
                $baseFormDTO->relatedPokemon,
                fn(PokemonDTO $p) => $p->id === $pokemon->getId()
            );
            $target = current($target);
            $unTarget = array_filter(
                $baseFormDTO->relatedPokemon,
                fn(PokemonDTO $p) => $p->id !== $pokemon->getId()
            );
            $baseFormDTO->relatedPokemon = [];
            $target->relatedPokemon = [$baseFormDTO, ...$unTarget];
            $captured = [$target];
        }

        return $this->json($captured);
    }

    #[Route('/generation-api/{genNumber}', name: 'app_generation_api', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function generationApi(Generation $generation): Response
    {
        $user = $this->getUser();
        $pokemons = $generation->getPokemon()->getValues();
        $captured = $this->capturedPokemonService->userCapturedByGeneration($user, $pokemons);

        return $this->json($captured);
    }

    #[Route('/search-api', name: 'app_search_api', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function search(Request $request): Response
    {
        $user = $this->getUser();
        /** @var PokemonRepository $pokemonRepository */
        $pokemonRepository = $this->entityManager->getRepository(Pokemon::class);
        $pokemons = $pokemonRepository->searchByName($request->get('search'));
        $captured = $this->capturedPokemonService->userCapturedByGeneration($user, $pokemons);

        return $this->json($captured);
    }

    #[Route('/capture-shop-api/', name: 'app_shop_api')]
    #[IsGranted('ROLE_USER')]
    public function shop(Request $request): Response
    {

        $itemRepo = $this->entityManager->getRepository(Items::class);
        /** @var User $user */
        $user = $this->getUser();
        $content = $request->getContent();
        $data = json_decode($content, true);
        $totalPrice = 0;
        $foundArray = [];
        foreach ($data['globalArray'] as $cartItem) {
            $foundItem = $itemRepo->find($cartItem['id']);
            $quantity = $cartItem['quantity'];
            $foundArray[] = ['item' => $foundItem, 'quantity' => $quantity];
            if ($foundItem !== null && $foundItem->isActive() === true) {
                $price = $foundItem->getPrice();
                $totalPrice += $price * $quantity;
            } else {
                return $this->json([
                    'error' => 'Une erreur s\'est produite, veuillez réessayer plus tard.'
                ]);
            }
        }

        $userWallet = $user->getMoney();
        if ($userWallet < $totalPrice) {
            return $this->json([
                'error' => 'Vous n\'avez pas assez d\'argent pour acheter ces objets.',
            ]);
        }

        foreach ($foundArray as $itemInfo) {
            $userItemRepo = $this->entityManager->getRepository(UserItems::class);
            $item = $itemInfo['item'];
            $alreadyExist = $userItemRepo->findOneBy(['item' => $item->getId(), 'user' => $user->getId()]);

            if ($alreadyExist === null) {
                $userItem = new UserItems();
                $userItem->setItem($item);
                $userItem->setUser($user);
                $userItem->setQuantity($itemInfo['quantity']);
                $user->addUserItem($userItem);
                $this->entityManager->persist($userItem);
            } else {
                /* @var $alreadyExist UserItems */
                $alreadyExist->setQuantity($alreadyExist->getQuantity() + $itemInfo['quantity']);
                $this->entityManager->persist($alreadyExist);
            }
        }

        $user->setMoney($user->getMoney() - $totalPrice);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'success' => 'Votre achat a bien été effectué!',
            'kartPrice' => $totalPrice,
            'array' => $foundArray
        ]);
    }

    #[Route('/mon-profil-api', name: 'app_profil_api')]
    #[IsGranted('ROLE_USER')]
    public function profilApi(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $avatarId = $request->get('avatarId');
        $user->setAvatar($avatarId);

        // Enregistre les changements en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'avatarId' => $user->getAvatar(),
            'error' => 'Erreur lors du changement d\'avatar!',
            'success' => 'Votre avatar a bien été changé !',
        ]);
    }
}
