<?php


namespace App\Controller;

use App\Entity\CapturedPokemon;
use App\Entity\Items;
use App\Entity\Pokemon;
use App\Form\EditModifyProfilFormType;
use App\Entity\User;
use DateTime;
use App\Form\ModifyFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;



class HomeController extends AbstractController
{
    // Annotation qui permet à Symfony de retrouver quelle route correspond à quelle fonction 
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        $user = $this->getUser();
        if ($user) {
            $this->addFlash('success', sprintf('Bonjour %s', $user->getPseudonym()));
        }


        return $this->render('main/home.html.twig', [
        ]);
    }


    #[Route('/mon-profil/', name: 'app_profil')]
    #[IsGranted('ROLE_USER')]
    public function profil(ManagerRegistry $doctrine): Response
    {

        // Repos
        $userRepo = $doctrine->getRepository(User::class);
        $pokeRepo = $doctrine->getRepository(Pokemon::class);

        // Utilisateur connecté
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


    #[Route('/mon-profil-api/', name: 'app_profil_api')]
    #[IsGranted('ROLE_USER')]
    public function profilApi(Request $request, ManagerRegistry $doctrine): Response
    {

        $user = $this->getUser();

        $avatarId = $request->get('avatarId');

        $user->setAvatar($avatarId);

        // Enregistre les changements en base de données
        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();


        return $this->json([
            'avatarId' => $user->getAvatar(),
            'error' => 'Erreur lors du changement d\'avatar!',
            'success' => 'Votre avatar a bien été changé !',
        ]);

    }


    #[Route('/capture/', name: 'app_capture')]
    #[IsGranted('ROLE_USER')]
    public function capture(ManagerRegistry $doctrine): Response
    {

        $user = $this->getUser();
        $pokeRepo = $doctrine->getRepository(Pokemon::class);
        $capturedPokeRepo = $doctrine->getRepository(CapturedPokemon::class);
        $userRepo = $doctrine->getRepository(User::class);

        $allUser = $userRepo->findAll();

        $totalPokemon = 0;

        foreach ($allUser as $user){

            $userLaunch = $user->getLaunchCount();

            $totalPokemon = $totalPokemon + $userLaunch;

        }





        //Coté Shop

        //Envoi de la liste des articles

        $itemsRepo = $doctrine->getRepository(Items::class);

        $items = $itemsRepo->findAll();


        return $this->render('main/capture.html.twig', [

            'totalPokemon' => $totalPokemon,
            'items' => $items,

        ]);


    }

    #[Route('/capture-shop-api/', name: 'app_shop_api')]
    #[IsGranted('ROLE_USER')]
    public function shop(Request $request, ManagerRegistry $doctrine):Response {

        $userRepo = $doctrine->getRepository(User::class);
        $itemRepo = $doctrine->getRepository(Items::class);

        $user = $this->getUser();

        $kartString = $request->get('quantityArray');

        $kart = explode(",", $kartString);

        $allItems = $itemRepo->findAll();

        $totalPrice = 0;

        //Comptage du panier

        foreach ($allItems as $item) {

            $unityPrice = $item->getPrice();

            $kartItemPrice = $unityPrice * intval($kart[$item->getId()-1]);

            $totalPrice = $totalPrice + $kartItemPrice;
        }

        $userWallet = $this->getUser()->getMoney();


        if ($userWallet<$totalPrice){

            return $this->json([
                'error' => 'Vous n\'avez pas assez d\'argent pour acheter ce lot.',
            ]);

        }else{

            //On enlève l'argent de l'utilisateur


            $user->setMoney($user->getMoney()-$totalPrice);



            //Si l'utilisateur à assez d'argent

            $user->setHyperBall($user->getHyperBall()+intval($kart[0]));

            $user->setShinyBall($user->getShinyBall()+intval($kart[1]));

            $user->setMasterBall($user->getMasterBall()+intval($kart[2]));


            $em = $doctrine->getManager();
            $em->flush();


            return $this->json([

                'success'=> 'Votre achat a bien été effectué!',
                'kart' => $kart,
                'kartPrice' => $totalPrice,


            ]);

        }
    }



    #[Route('/capture-api/', name: 'app_capture_api')]
    #[IsGranted('ROLE_USER')]
    public function captureApi(ManagerRegistry $doctrine, Request $request): Response
    {

        $pokeRepo = $doctrine->getRepository(Pokemon::class);

        $pokeballId = intval($request->get('pokeballData'));


        $user = $this->getUser();



        //Calcul des probabilités

        $randomRarity = rand(10, 1000) / 10;


        //LANCERS NORMAUX ----------------------

        if ($pokeballId == 1){

            if ($user->getLaunchs() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }


            $rarity = $this->getStr($randomRarity);

            //Calculs shiny


            $shinyTest = rand(1, 200);


            if ($shinyTest == 1) {

                $isShiny = true;
            } else {

                $isShiny = false;
            }


            //On retire un lancer à l'utilisateur
            $this->getUser()->setLaunchs($this->getUser()->getLaunchs()-1);



        }elseif ($pokeballId == 2){


            //HYPER BALL------------------------


            if ($user->getHyperBall() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }



            //Calcul Rareté


            if ($randomRarity <= 70 ) {

                $rarity = 'TR';
                //70%

            } elseif ($randomRarity > 70 && $randomRarity <= 90){

                $rarity = 'ME';
                //30%

            }else{

                $rarity = 'SR';

            }


            //Calculs shiny


            $shinyTest = rand(1, 200);


            if ($shinyTest == 1) {

                $isShiny = true;
            } else {

                $isShiny = false;
            }



            //On retire un lancer à l'utilisateur
            $this->getUser()->setHyperBall($this->getUser()->getHyperBall()-1);




        }elseif ($pokeballId == 3){


            if ($user->getShinyBall() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }



            //SHINY BALL---------------------------

            $rarity = $this->getStr($randomRarity);


            //Shiny 100%


            $isShiny = true;

            //On retire un lancer à l'utilisateur
            $this->getUser()->setShinyBall($this->getUser()->getShinyBall()-1);


        }elseif ($pokeballId == 4){

            //MASTER BALL --------------------------


            if ($user->getMasterBall() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'

                ]);
            }



            if ($randomRarity <= 70 ) {

                $rarity = 'EX';
                //70%

            } else {

                $rarity = 'UR';
                //30%

            }

            //Calculs shiny


            $shinyTest = rand(1, 200);


            if ($shinyTest == 1) {

                $isShiny = true;
            } else {

                $isShiny = false;
            }


            //On retire un lancer à l'utilisateur
            $this->getUser()->setMasterBall($this->getUser()->getMasterBall()-1);


        }else{

            return $this->json([
                'error' => 'Lancer invalide.',
                'bug' => $pokeballId,
            ]);


        }





        //Recherche du pokémon

        $pokemons = $pokeRepo->findByRarity($rarity);

        $randomPoke = rand(0, count($pokemons) - 1);

        $pokemonSpeciesCaptured = $pokemons[$randomPoke];

        $pokemonCaptured = new CapturedPokemon();





        //Hydratation BDD
        $pokemonCaptured
            ->setPokemon($pokemonSpeciesCaptured)
            ->setOwner($this->getUser())
            ->setCaptureDate(new DateTime())
            ->setShiny($isShiny);


        //Voir si un dresseur a deja vu ce pokémon ou pas

        $user = $this->getUser();

        $alreadyCapturedPokemon = $pokeRepo->getSpeciesEncounter($user);

        $pokeID = [];

        foreach ($alreadyCapturedPokemon as $acp){
            $pokeID[] = $acp->getPokeId();
        }

        $pokemonCapturedId = $pokemonCaptured->getPokemon()->getPokeId();

        if (in_array($pokemonCapturedId, $pokeID)){
            $isNew = false;
        }else{
            $isNew = true;
        }





        if ($isNew || $isShiny){
        //Hydratation BDD si le Pokémon est nouveau ou shiny

            $em = $doctrine->getManager();

            $em->persist($pokemonCaptured);

            $em->flush();

        }else{

            //Valeur en pièce si le Pokémon à déja été vu

            $rarityScale = [
                'C' => 1,
                'PC' => 3,
                'R' => 5,
                'TR' => 10,
                'ME' => 25,
                'SR' => 50,
                'EX' => 50,
                'UR' => 250
            ];

            $capturedRarity = $pokemonCaptured->getPokemon()->getRarity();

            $this->getUser()->setMoney($this->getUser()->getMoney()+$rarityScale[$capturedRarity]);

        }


        //On compte un lancer en plus pour l'utilisateur
        $this->getUser()->setLaunchCount($this->getUser()->getLaunchCount()+1);


        //UPDATE user
        //SET launch_count = (
        //    SELECT COUNT(*)
        //    FROM captured_pokemon
        //    WHERE captured_pokemon.owner_id = 3
        //    AND captured_pokemon.owner_id = user.id
        //)
        //WHERE pseudonym = 'Spirit';



        $em = $doctrine->getManager();
        $em->flush();




        //Retour des informations a Javascript

        return $this->json([
            'captured_pokemon' => [
                'id' => $pokemonCaptured->getPokemon()->getId(),
                'name' => $pokemonCaptured->getPokemon()->getName(),
                'gif' => $pokemonCaptured->getPokemon()->getGif(),
                'type' => $pokemonCaptured->getPokemon()->getType(),
                'type2' => $pokemonCaptured->getPokemon()->getType2(),
                'description' => $pokemonCaptured->getPokemon()->getDescription(),
                'shiny' => $pokemonCaptured->getShiny(),
                'rarity' => $rarity,
                'rarityRandom' => $randomRarity,
                'new' => $isNew,
            ],
        ]);

    }


    #[Route('/pokedex/', name: 'app_pokedex')]
    #[IsGranted('ROLE_USER')]
    public function pokedex( ManagerRegistry $doctrine): Response
    {



        $pokeRepo = $doctrine->getRepository(Pokemon::class);

        $pokemons = $pokeRepo->findBy([], ['pokeId' => 'ASC']);

        $pokemonsCaptured = $pokeRepo->getSpeciesEncounter($this->getUser());

        $shinyObtained = $pokeRepo->getShinyCaptured($this->getUser());
        

        // Créer un tableau contenant les informations de tous les pokémons
        $allPokemonInfo = [];
        foreach ($pokemons as $poke) {
            $allPokemonInfo[] = [
                'id' => $poke->getId(),
                'pokeId' => $poke->getPokeId(),
                'name' => $poke->getName(),
                'rarity' => $poke->getRarity(),
                'captured' => false, // initialisé à false
                'shiny' => false,
            ];
        }

        // Mettre à jour le tableau pour les pokémons capturés par l'utilisateur
        foreach ($allPokemonInfo as &$pokeInfo) {

            //vérification si l'utilisateur à libéré le pokemon
            foreach ($pokemonsCaptured as $captured) {
                if ($pokeInfo['id'] === $captured->getId()) {
                    $pokeInfo['captured'] = true; // mettre à jour à true
                }
            }
            //vérification si l'utilisateur le possède en shiny
            foreach ($shinyObtained as $shinies){

                if ($pokeInfo['pokeId'] === $shinies['pokeId']){
                    $pokeInfo['shiny'] = true;

                }

            }

        }



        return $this->render('main/pokedex.html.twig', [
            'pokemons' => $allPokemonInfo,
            'pokemonsCaptured' => $pokemonsCaptured,
        ]);
    }

    #[Route('/pokedex-api/', name: 'app_pokedex_api')]
    #[IsGranted('ROLE_USER')]
    public function pokedexApi(Request $request, ManagerRegistry $doctrine ): Response
    {

        $user = $this->getUser();

        $pokeRepo = $doctrine->getRepository(Pokemon::class);

        $pokemonPokeId = $request->get('pokemonId');



        // Récupération du Pokémon correspondant à l'ID envoyé depuis le JSON
        $pokemonToDisplay = $pokeRepo->findOneBy(['pokeId' => $pokemonPokeId]);


         $shinyObtained = $pokeRepo->getShinyCaptured($user);

         $isShiny = false;

        //Si l'utilisateur possède au moins un pokémon shiny, on compare les pokéID avec celui récupéré en requête

         if ($shinyObtained) {

             foreach ($shinyObtained as $shiny) {

                 foreach ($shiny as $shinyId) {

                     if ($shinyId == $pokemonPokeId) {

                         $isShiny = true;

                     }

                 }

             }

         }



        if ($pokemonToDisplay !== null) {

            return $this->json([
                'pokemonToDisplay' => [
                    'pokeId' => $pokemonToDisplay->getPokeId(),
                    'name' => $pokemonToDisplay->getName(),
                    'nameEN' => $pokemonToDisplay->getNameEn(),
                    'gif' => $pokemonToDisplay->getGif(),
                    'type1' => $pokemonToDisplay ->getType(),
                    'type2' => $pokemonToDisplay->getType2(),
                    'description' => $pokemonToDisplay->getDescription(),
                    'shiny' => $isShiny,
                ]
            ]);

        } else {

            // Si le résultat est nul, retourner une réponse d'erreur
            return $this->json([
                'error' => 'Impossible d\'accéder au pokémon séléctionné',
            ]);
        }


    }



    #[Route('/modify-profil/', name: 'app_modify')]
    #[IsGranted('ROLE_USER')]
    public function modifyProfil(UserPasswordHasherInterface $encoder, Request $request, ManagerRegistry $doctrine,): Response
    {
        /**
         * page de modification du profil de l'utilisateur (pseudonym et mot de passe).
         */

        $connectedUser = $this->getUser();


        $form = $this->createForm(EditModifyProfilFormType::class, $connectedUser);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // vérifier si le mot de passe actuel est correct

            $currentPassword = $form->get('currentPassword')->getData();

            if (!$encoder->isPasswordValid($connectedUser, $currentPassword)) {

                $form->get('currentPassword')->addError( new FormError('Mauvais mot de passe !') );


            } else {

                // Modification du profil
                $newPassword = $form->get('plainPassword')->getData();
                $hashNewPassword = $encoder->hashPassword($connectedUser, $newPassword);
                $connectedUser->setPassword($hashNewPassword);

                $em = $doctrine->getManager();
                $em->flush();

                // message flash de succès
                $this->addFlash('success', 'Votre profil a été modifié avec succès');

                return $this->redirectToRoute('app_profil');
            }
        }

        return $this->render('main/modify_profil.html.twig', [
            'editModifyProfilForm' => $form->createView(),
        ]);
    }



    #[Route('/types/', name: 'app_types')]
    public function types(): Response
    {
        return $this->render('main/types.html.twig', [
        ]);
    }

    #[Route('/about/', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('main/about.html.twig', [
        ]);
    }

    #[Route('/project/', name: 'app_project')]
    public function project(): Response
    {
        return $this->render('main/project.html.twig', [
        ]);
    }

    #[Route('/mentions-legales/', name: 'app_legals')]
    public function legals(): Response
    {
        return $this->render('main/legals.html.twig', [

        ]);
    }

    /**
     * @param float|int $randomRarity
     * @return string
     */
    public function getStr(float|int $randomRarity): string
    {
        if ($randomRarity < 40) {

            $rarity = 'C';
            //40%

        } elseif ($randomRarity >= 40 && $randomRarity < 70) {

            $rarity = 'PC';
            //30%

        } elseif ($randomRarity >= 70 && $randomRarity < 90) {

            $rarity = 'R';
            //20%

        } elseif ($randomRarity >= 90 && $randomRarity < 98) {

            $rarity = 'TR';
            //8%
        } elseif ($randomRarity >= 98 && $randomRarity < 99) {

            $rarity = 'ME';
            //1%
        } elseif ($randomRarity >= 99 && $randomRarity < 99.5) {

            $rarity = 'EX';
            //0.5%
        } elseif ($randomRarity >= 99.5 && $randomRarity < 100) {

            $rarity = 'SR';
            //0.5%
        } else {

            $rarity = 'UR';
            //0.01%

        }
        return $rarity;
    }


}


