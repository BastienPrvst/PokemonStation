<?php

namespace App\Event\Listener;


use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class UpdateLaunchListener{

    private ManagerRegistry $doctrine;
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, ManagerRegistry $doctrine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
    }

    public function onKernelRequest(RequestEvent $event): void
    {

        //Si l'utilisateur n'est pas connecté, l'ecouteur d'évenement s'arrête ici
        if(!$event->isMainRequest() || $this->tokenStorage->getToken() == null){
            return;
        }

        //Recuperation du token de connexion de l'utilisateur connecté
        $user = $this->tokenStorage->getToken()->getUser();

        //Si l'utilisateur à plus de 100 lancers, l'ecouteur d'évenement s'arrête ici
        if ($user->getLaunchs() >= 100 ){
            return;
        }

        //Recuperation de la date par rapport au dernier lancer de l'utilisateur en timestamp(seconde)
        $lastObtainedLaunchTimestamp = $user->getLastObtainedLaunch()->format('U');

        //Recuperation de la date actuelle
        $currentTimestamp = time();

        //Calcul de la difference des deux
        $difference = $currentTimestamp - $lastObtainedLaunchTimestamp;

        //Si la différence est plus grande, on augmente le nombre de pokeball par temps de fois ou cette difference est effective
        if($difference >= 1800){

            $numberOfLaunchs = (int) floor($difference / 1800);

            $secondsLeft = $difference % 1800;


//            //Blocage "cap" a 100 pokeballs
            if ($numberOfLaunchs + $user->getLaunchs() > 100){

                $numberOfLaunchs = 100 - $user->getLaunchs();

                $secondsLeft = 0;

            }

            $user->setLaunchs( $user->getLaunchs() + $numberOfLaunchs );

            $user->setLastObtainedLaunch( new \DateTime('- ' . $secondsLeft . ' seconds') );

            $this->doctrine->getManager()->flush();

        }

    }

}