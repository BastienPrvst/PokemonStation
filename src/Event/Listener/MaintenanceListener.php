<?php

namespace App\Event\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceListener
{
    public function __construct(
        private $maintenance,
        private readonly Environment $twig
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
