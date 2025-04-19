<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditModifyProfilFormType;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    public function __construct(
        private readonly PokemonRepository $pokemonRepository,
        private readonly EntityManagerInterface $entityManager,
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

        $allGens = $this->pokemonRepository->pokemonSeenByGen($user);
        $allTrueGens = $this->pokemonRepository->pokemonSeenByGenTrue($user);

        for ($i = 0, $iMax = count($allGens); $i < $iMax; $i++) {
            $allGens[$i]['true_gen_captured'] = $allTrueGens[$i]['true_gen_captured'];
        }

        return $this->render(
            'main/profile.html.twig',
            [...$this->prepareUserInfo($user),
                ...['avatars' => $allAvatars,
                'allGens' => $allGens]],
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
            [...$this->prepareUserInfo($user),
                    'allGens' => $allGens],
        );
    }

    private function prepareUserInfo(User $user): array
    {
        return [
            'nbPokemon'          => $this->pokemonRepository->getCountEncounteredBy($user),
            'nbPokemonUnique'    => $this->pokemonRepository->getCountUniqueEncounteredBy($user),
            'nbShiny'            => $this->pokemonRepository->getCountShiniesEncounteredBy($user),
            'nbTR'               => $this->pokemonRepository->getCountByRarityEncounteredBy($user, 'TR'),
            'nbEX'               => $this->pokemonRepository->getCountByRarityEncounteredBy($user, 'EX'),
            'nbSR'               => $this->pokemonRepository->getCountByRarityEncounteredBy($user, 'SR'),
            'nbUR'               => $this->pokemonRepository->getCountByRarityEncounteredBy($user, 'UR'),
            'pokedexSize'        => $this->pokemonRepository->getFullPokedexSize(),
            'user'               => $user,
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
}
