<?php

namespace App\Event\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class MaintenanceListener{

    private $maintenance;

    public function __construct($maintenance){
        $this->maintenance = $maintenance;
    }


    public function onKernelRequest(RequestEvent $event): void{


        //On vérifie si le fichier .maintenance n'existe pas
        if(!file_exists($this->maintenance)){
            return;
        }

        //Le fichier existe

        //On définit la réponse

        $event->setResponse(
            new Response(
                'Le site est en maintenance',
                Response::HTTP_SERVICE_UNAVAILABLE
            )
        );
        //On stoppe le traitement des évènements

        $event->stopPropagation();

    }

}