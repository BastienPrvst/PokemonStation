<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * fonction de la page de connexion.
     */
    #[Route(path: '/connexion', name: 'app_connexion')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();



        return $this->render('security/connexion.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/deconnexion', name: 'app_deconnexion')]
    public function logout(): void
    {
        //le code ici ne sera jamais lu, car la page de déconnexion est deja géré en interne par le bundle security
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
