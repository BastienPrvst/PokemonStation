<?php

namespace App\Controller;

use App\Entity\CapturedPokemon;
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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class APIController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
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

    #[Route('/pokedex-api', name: 'app_pokedex_api')]
    #[IsGranted('ROLE_USER')]
    public function pokedexApi(Request $request): Response
    {
        /* @var User $user */
        $user = $this->getUser();
        $pokeRepo = $this->entityManager->getRepository(Pokemon::class);
        $cpRepo = $this->entityManager->getRepository(CapturedPokemon::class);
        $pokemonPokeId = $request->get('pokemonId');
        $pokemonToDisplay = $pokeRepo->findOneBy(['pokeId' => $pokemonPokeId]);
        $shinyObtained = $cpRepo->findShinyCaptured($user);
        $isShiny = false;

        //Si l'utilisateur possède au moins un pokémon shiny, on compare les pokéID avec celui récupéré en requête
        if (!empty($shinyObtained) && in_array($pokemonPokeId, $shinyObtained, true)) {
            $isShiny = true;
        }

        if ($pokemonToDisplay !== null) {
            return $this->json([
                'pokemonToDisplay' => [
                    'pokeId' => $pokemonToDisplay->getPokeId(),
                    'name' => $pokemonToDisplay->getName(),
                    'nameEN' => $pokemonToDisplay->getNameEn(),
                    'type1' => $pokemonToDisplay->getType(),
                    'type2' => $pokemonToDisplay->getType2(),
                    'description' => $pokemonToDisplay->getDescription(),
                    'shiny' => $isShiny,
                ]
            ]);
        }

        return $this->json([
            'error' => 'Impossible d\'accéder au pokémon séléctionné',
        ]);
    }

    #[Route('/generation-api/{id}', name: 'app_generation_api', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function generationApi(Generation $generation): Response
    {
        $user = $this->security->getUser();
        $pokemons = $generation->getPokemon()->getValues();
        $captured = $this->capturedPokemonService->userCapturedByGeneration($user, $pokemons);

        return $this->json($captured);
    }

    #[Route('/search-api', name: 'app_search_api', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function search(Request $request): Response
    {
        $user = $this->security->getUser();
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
