<?php

// src/Recaptcha/RecaptchaValidator.php

namespace App\Recaptcha;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service servant à vérifier auprès des serveurs de Google si un captcha a correctement été validé
 * Pour fonctionner, le service a besoin de la clé privée présente dans le fichier .env .
 * C'est pour ça que nous demandant à Symfony de nous fournir le service ParameterBagInterface qui nous permettra d'accèder aux paramètres globaux du site
 */
class RecaptchaValidator{

    /**
     * Stockage du service ParameterBagInterface de Symfony pour accèder aux paramètres globaux du site
     */
    private ParameterBagInterface $params;

    /**
     * Constructeur de la classe qui servira à demander le service ParameterBagInterface à Symfony et à hydrater $params avec ce dernier
     */
    public function __construct(ParameterBagInterface $params){
        $this->params = $params;
    }


    /**
     * Méthode servant à vérifier auprès de Google si le code captcha $recaptchaResponse et l'adresse IP $ip correspondent à une captcha correctement validé
     * La méthode renverra true si le captcha est correcte, sinon false
     */
    public function verify(?string $recaptchaResponse, ?string $ip = null): bool
    {

        if(empty($recaptchaResponse)) {
            return false;
        }
        $params = [
            'secret'    => $this->params->get('google_recaptcha.private_key'),
            'response'  => $recaptchaResponse
        ];
        if($ip){
            $params['remoteip'] = $ip;
        }
        $url = "https://www.google.com/recaptcha/api/siteverify?" . http_build_query($params);
        if(function_exists('curl_version')){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
        }else{
            $response = file_get_contents($url);
        }
        if(empty($response) || is_null($response)){
            return false;
        }
        $json = json_decode($response);
        return $json->success;

    }

}