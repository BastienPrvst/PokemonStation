<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Entity\Items;
use App\Entity\Pokemon;
use App\Entity\User;
use App\Repository\GenerationRepository;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PokemonController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/pokedex/', name: 'app_pokedex')]
    #[IsGranted('ROLE_USER')]
    public function pokedex(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var PokemonRepository $pokeRepo */
        $pokeRepo = $this->entityManager->getRepository(Pokemon::class);
        /** @var GenerationRepository $genRepo */
        $genRepo = $this->entityManager->getRepository(Generation::class);

        $pokemons = $pokeRepo->findBy([], ['pokeId' => 'ASC']);
        $pokemonsCaptured = $pokeRepo->getSpeciesEncounter($user);
        $pokemonShiniesCaptured = $pokeRepo->getShinySpeciesEncounter($user);
        $rawGenerations = $genRepo->findAll();

        $specialGroup = ['ME', 'SR', 'UR', 'GMAX'];

        foreach ($rawGenerations as $generation) {
            $generationId = $generation->getId();

            foreach ($pokemons as $poke) {
                $pokeRarity = $poke->getRarity();
                $key = $generationId;

                // grouper à part les ME, SR, UR, GMAX
                if (in_array($pokeRarity, $specialGroup)) {
                    $key = $pokeRarity;
                // grouper à part les formes régionnales
                } else if ($poke->getRelateTo()) {
                    $key = 'ALT';
                }

                $generations[$key][] = [
                    'id'       => $poke->getId(),
                    'pokeId'   => $poke->getPokeId(),
                    'name'     => $poke->getName(),
                    'rarity'   => $poke->getRarity(),
                    'captured' => in_array($poke, $pokemonsCaptured),
                    'shiny'    => in_array($poke, $pokemonShiniesCaptured),
                ];
            }
        }

        return $this->render('main/pokedex.html.twig', ['generations' => $generations]);
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
