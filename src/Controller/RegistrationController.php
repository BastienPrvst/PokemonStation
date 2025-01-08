<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Recaptcha\RecaptchaValidator;  // Importation de notre service de validation du captcha
use Symfony\Component\Form\FormError;  // Importation de la classe permettant de créer des erreurs dans les formulaires



class RegistrationController extends AbstractController
{
    /**
     * Contrôleur de la page d'inscription
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManager
     * @param $recaptcha
     * @return Response
     */
    #[Route('/register/', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,RecaptchaValidator $recaptcha): Response
    {

        // si l'utilisateur est deja connecter, on le redirige de force sur la page d'acceuil du site

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        // Création d'un nouvel objet utilisateur
        $user = new User();

        // Création d'un nouveau formulaire de création de compte, "branché" sur $user (pour l'hydrater)
        $form = $this->createForm(RegistrationFormType::class, $user);




        // Remplissage du formulaire avec les données POST (qui sont dans request)
        $form->handleRequest($request);

        //Si le formulaire a bien été envoyé
        if ($form->isSubmitted()) {


            //Récupération de la valeur du captcha ($_POST['g-recaptcha-response'])
            $recaptchaResponse = $request->request->get('g-recaptcha-response', null);

            //si le captcha est null ou s'il est invalide on ajoute une erreur dans le formulaire

            if($recaptchaResponse == null || !$recaptcha->verify( $recaptchaResponse, $request->server->get('REMOTE_ADDR') )){

                // Ajout d'une nouvelle erreur manuellement dans le formulaire
                $form->addError(new FormError('Le Captcha doit être validé !'));
            }

            if ($form->isValid()){
                // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()

                )
            )   //Mise en place d'un nombre de lancers par défaut quand on crée un compte
                ->setLaunchs(30)
                ->setLastObtainedLaunch(new \DateTime())
                ->setAvatar('1')
                ->setHyperBall(0)
                ->setMoney(0)
                ->setMasterBall(0)
                ->setShinyBall(0)

            ;



            //hydratation de la date d'inscription du nouvel utilisateur

                $user->setCreationDate(new \DateTime);

                $entityManager->persist($user);

                $entityManager->flush();


                $this->addFlash('success', 'Votre compte à bien été créé!');

            return $this->redirectToRoute('app_connexion');




        }

    }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }




}
