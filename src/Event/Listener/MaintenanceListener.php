<?php

namespace App\Event\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class MaintenanceListener
{
    public function __construct(
        private $maintenance,
        private readonly Environment $twig,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }


    public function onKernelRequest(RequestEvent $event): void
    {

        //On vérifie si le fichier .maintenance n'existe pas
        if (!file_exists($this->maintenance)) {
            return;
        }

        //Le fichier existe
        //On définit la réponse

        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() && in_array('ROLE_SUPER_ADMIN', $token->getRoleNames(), true)) {
            return;
        }

        $event->setResponse(
            new Response(
                $this->twig->render('maintenance.html.twig'),
                Response::HTTP_SERVICE_UNAVAILABLE
            )
        );

        //On stoppe le traitement des évènements
        $event->stopPropagation();
    }
}
