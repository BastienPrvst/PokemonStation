<?php

namespace App\Controller;

use App\Entity\Pokemon;
use App\Entity\User;
use App\Form\EditModifyProfilFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/mon-profil/', name: 'app_profil')]
    #[IsGranted('ROLE_USER')]
    public function profil(ManagerRegistry $doctrine): Response
    {
        $userRepo = $doctrine->getRepository(User::class);
        $pokeRepo = $doctrine->getRepository(Pokemon::class);
        /** @var User $currentConnectedUser */
        $currentConnectedUser = $this->getUser();

        return $this->render('main/profil.html.twig', [
            'nbPokemon' => $pokeRepo->getCountEncounteredBy($currentConnectedUser),
            'nbPokemonUnique' => $pokeRepo->getCountUniqueEncounteredBy($currentConnectedUser),
            'nbShiny' => $pokeRepo->getCountShiniesEncounteredBy($currentConnectedUser),
            'nbTR' => $pokeRepo->getCountByRarityEncounteredBy($currentConnectedUser, 'TR'),
            'nbEX' => $pokeRepo->getCountByRarityEncounteredBy($currentConnectedUser, 'EX'),
            'nbSR' => $pokeRepo->getCountByRarityEncounteredBy($currentConnectedUser, 'SR'),
            'topUserSpeciesSeen' => $userRepo->top10TotalSpeciesSeen(),
            'pokedexSize' => $pokeRepo->getFullPokedexSize(),
        ]);
    }

    #[Route('/modify-profil/', name: 'app_modify')]
    #[IsGranted('ROLE_USER')]
    public function modifyProfil(
        UserPasswordHasherInterface $encoder,
        Request                     $request,
        ManagerRegistry             $doctrine
    ): Response {
        /**
         * page de modification du profil de l'utilisateur (pseudonym et mot de passe).
         */
        /** @var User $currentConnectedUser */
        $connectedUser = $this->getUser();
        $form = $this->createForm(EditModifyProfilFormType::class, $connectedUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // vérifier si le mot de passe actuel est correct
            $currentPassword = $form->get('currentPassword')->getData();

            if (!$encoder->isPasswordValid($connectedUser, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Mauvais mot de passe !'));
            } else {
                // Modification du profil
                $newPassword = $form->get('plainPassword')->getData();
                $hashNewPassword = $encoder->hashPassword($connectedUser, $newPassword);
                $connectedUser->setPassword($hashNewPassword);
                $em = $doctrine->getManager();
                $em->flush();

                // Message flash de succès
                $this->addFlash('success', 'Votre profil a été modifié avec succès');

                return $this->redirectToRoute('app_profil');
            }
        }

        return $this->render('main/modify_profil.html.twig', [
            'editModifyProfilForm' => $form->createView(),
        ]);
    }
}
