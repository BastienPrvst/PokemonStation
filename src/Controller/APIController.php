<?php

namespace App\Controller;

use App\Entity\CapturedPokemon;
use App\Entity\Pokemon;
use App\Entity\User;
use App\Service\PokemonOdds;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function PHPUnit\Framework\isEmpty;

class APIController extends AbstractController
{
    public function __construct(private readonly PokemonOdds $pokemonOdds)
    {
    }


    #[Route('/capture-api/', name: 'app_capture_api')]
    #[IsGranted('ROLE_USER')]
    public function captureApi(Request $request): Response
    {
        $pokeballId = (int)$request->get('pokeballData');
        /** @var User $user * */
        $user = $this->getUser();
        return $this->pokemonOdds->calculationOdds($user, $pokeballId);
    }

    #[Route('/pokedex-api/', name: 'app_pokedex_api')]
    #[IsGranted('ROLE_USER')]
    public function pokedexApi(Request $request, ManagerRegistry $doctrine): Response
    {
        /* @var User $user */
        $user = $this->getUser();
        $pokeRepo = $doctrine->getRepository(Pokemon::class);
        $cpRepo = $doctrine->getRepository(CapturedPokemon::class);
        $pokemonPokeId = $request->get('pokemonId');
        $pokemonToDisplay = $pokeRepo->findOneBy(['pokeId' => $pokemonPokeId]);
        $shinyObtained = $cpRepo->findShinyCaptured($user);
        $isShiny = false;

        //Si l'utilisateur possède au moins un pokémon shiny, on compare les pokéID avec celui récupéré en requête
        if (!isEmpty($shinyObtained) && in_array($pokemonPokeId, $shinyObtained, true)) {
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

//    #[Route('/capture-shop-api/', name: 'app_shop_api')]
//    #[IsGranted('ROLE_USER')]
//    public function shop(Request $request, ManagerRegistry $doctrine): Response
//    {
//        $itemRepo = $doctrine->getRepository(Items::class);
//        /** @var User $user */
//        $user = $this->getUser();
//        $kartString = $request->get('quantityArray');
//        $kart = explode(",", $kartString);
//        $allItems = $itemRepo->findAll();
//        $totalPrice = 0;
//
//        //Comptage du panier
//
//        foreach ($allItems as $item) {
//            $unityPrice = $item->getPrice();
//            $kartItemPrice = $unityPrice * (int)$kart[$item->getId() - 1];
//            $totalPrice += $kartItemPrice;
//        }
//
//        $userWallet = $this->getUser()->getMoney();
//        if ($userWallet < $totalPrice) {
//            return $this->json([
//                'error' => 'Vous n\'avez pas assez d\'argent pour acheter ce lot.',
//            ]);
//        }
//
//        //On enlève l'argent de l'utilisateur
//        $user->setMoney($user->getMoney() - $totalPrice);
//
//        //Si l'utilisateur à assez d'argent
//        $user->setHyperBall($user->getHyperBall() + (int)$kart[0]);
//        $user->setShinyBall($user->getShinyBall() + (int)$kart[1]);
//        $user->setMasterBall($user->getMasterBall() + (int)$kart[2]);
//        $em = $doctrine->getManager();
//        $em->flush();
//
//        return $this->json([
//            'success' => 'Votre achat a bien été effectué!',
//            'kart' => $kart,
//            'kartPrice' => $totalPrice,
//        ]);
//    }

    #[Route('/mon-profil-api/', name: 'app_profil_api')]
    #[IsGranted('ROLE_USER')]
    public function profilApi(Request $request, ManagerRegistry $doctrine): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $avatarId = $request->get('avatarId');
        $user->setAvatar($avatarId);

        // Enregistre les changements en base de données
        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json([
            'avatarId' => $user->getAvatar(),
            'error' => 'Erreur lors du changement d\'avatar!',
            'success' => 'Votre avatar a bien été changé !',
        ]);
    }
}
