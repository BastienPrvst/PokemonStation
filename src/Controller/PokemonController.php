<?php

namespace App\Controller;

use App\Entity\CapturedPokemon;
use App\Entity\Items;
use App\Entity\Pokemon;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PokemonController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager)
    {

    }

    #[Route('/pokedex/', name: 'app_pokedex')]
    #[IsGranted('ROLE_USER')]
    public function pokedex(): Response
    {
        /* @var User $user*/
        $user = $this->getUser();
        $cpRepo = $this->entityManager->getRepository(CapturedPokemon::class);
        $pokeRepo = $this->entityManager->getRepository(Pokemon::class);
        $pokemons = $pokeRepo->findBy([], ['pokeId' => 'ASC']);
        $pokemonsCaptured = $cpRepo->findSpeciesCaptured($user);
        $shinyObtained = $cpRepo->findShinyCaptured($user);

        // Créer un tableau contenant les informations de tous les pokémons
        $allPokemonInfo = [];
        foreach ($pokemons as $poke) {
            $allPokemonInfo[] = [
                'id' => $poke->getId(),
                'pokeId' => $poke->getPokeId(),
                'name' => $poke->getName(),
                'rarity' => $poke->getRarity(),
                'captured' => false, // initialisé à false
                'shiny' => false,
            ];
        }

        // Mettre à jour le tableau pour les pokémons capturés par l'utilisateur
        foreach ($allPokemonInfo as &$pokeInfo) {
            //vérification si l'utilisateur à libéré le pokemon
            if (in_array($pokeInfo['pokeId'], $pokemonsCaptured, true)) {
                $pokeInfo['captured'] = true;
            }
            //vérification si l'utilisateur le possède en shiny
            if (in_array($pokeInfo['pokeId'], $shinyObtained, true)) {
                $pokeInfo['shiny'] = true;
            }
        }

        return $this->render('main/pokedex.html.twig', [
            'pokemons' => $allPokemonInfo,
            'pokemonsCaptured' => $pokemonsCaptured,
        ]);
    }

    #[Route('/capture/', name: 'app_capture')]
    #[IsGranted('ROLE_USER')]
    public function capture(ManagerRegistry $doctrine): Response
    {
        $userRepo = $doctrine->getRepository(User::class);
        $allUser = $userRepo->findAll();
        $totalPokemon = 0;
        foreach ($allUser as $user) {
            $userLaunch = $user->getLaunchCount();
            $totalPokemon = $totalPokemon + $userLaunch;
        }

        //Coté Shop
        //Envoi de la liste des articles
        $itemsRepo = $doctrine->getRepository(Items::class);
        $items = $itemsRepo->findAll();

        return $this->render('main/capture.html.twig', [
            'totalPokemon' => $totalPokemon,
            'items' => $items,
        ]);
    }
}
