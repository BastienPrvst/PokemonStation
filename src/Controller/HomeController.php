<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use App\Repository\PokemonRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PokemonRepository $pokemonRepository
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function home(NewsRepository $newsRepository): Response
    {
        $allNews = $newsRepository->findRecent();

        return $this->render('main/home.html.twig', [
            'topUserSpeciesSeen' => $this->userRepository->top10TotalSpeciesSeen(),
            'pokedexSize'        => $this->pokemonRepository->getFullPokedexSize(),
            'allNews' => $allNews,
        ]);
    }

    #[Route('/types', name: 'app_types')]
    public function types(): Response
    {
        return $this->render('main/types.html.twig', [
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('main/about.html.twig', [
        ]);
    }

    #[Route('/project', name: 'app_project')]
    public function project(): Response
    {
        return $this->render('main/project.html.twig', [
        ]);
    }

    #[Route('/mentions-legales', name: 'app_legals')]
    public function legals(): Response
    {
        return $this->render('main/legals.html.twig', [
        ]);
    }
}
