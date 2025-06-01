<?php

namespace App\Controller;

use App\Entity\CapturedPokemon;
use App\Entity\Generation;
use App\Entity\Items;
use App\Entity\User;
use App\Repository\GenerationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PokemonController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/pokedex', name: 'app_pokedex')]
    #[IsGranted('ROLE_USER')]
    public function pokedex(): Response
    {
        /** @var GenerationRepository $genRepo */
        $genRepo = $this->entityManager->getRepository(Generation::class);
        $generations = $genRepo->findBy([], ['genNumber' => 'ASC']);

        return $this->render('main/pokedex.html.twig', [
            'generations'       => $generations,
        ]);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/capture', name: 'app_capture')]
    #[IsGranted('ROLE_USER')]
    public function capture(ManagerRegistry $doctrine): Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $doctrine->getRepository(User::class);
        $totalPokemon = $userRepo->totalPokemon();

        /** @var User $user */
        $user = $this->getUser();
        $allUserItems = $user->getUserItems();

        $fiveLast = $this->entityManager->getRepository(CapturedPokemon::class)->getLastRareCaptured();

        //Coté Shop
        $itemsRepo = $doctrine->getRepository(Items::class);
        $itemsToSell = $itemsRepo->findBy(["active" => true]);

        return $this->render('main/capture.html.twig', [
            'totalPokemon' => $totalPokemon,
            'allUserItems' => $allUserItems,
            'itemsToSell' => $itemsToSell,
            'fiveLast' => $fiveLast,
        ]);
    }

    #[Route(path: '/users-api', name: 'app_user_api')]
    #[IsGranted('ROLE_USER')]
    public function searchByPseudo(Request $request): JsonResponse
    {
        $pseudo = $request->get('search');
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['pseudonym' => $pseudo]);

        return $this->json($user?->getId());
    }
}
