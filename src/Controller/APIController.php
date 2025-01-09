<?php

namespace App\Controller;

use App\Entity\CapturedPokemon;
use App\Entity\Items;
use App\Entity\Pokemon;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class APIController extends AbstractController
{
    #[Route('/capture-api/', name: 'app_capture_api')]
    #[IsGranted('ROLE_USER')]
    public function captureApi(ManagerRegistry $doctrine, Request $request): Response
    {
        $pokeRepo = $doctrine->getRepository(Pokemon::class);
        $pokeballId = (int)$request->get('pokeballData');
        $user = $this->getUser();
        //Calcul des probabilités
        $randomRarity = random_int(10, 1000) / 10;

        //LANCERS NORMAUX ----------------------
        if ($pokeballId === 1) {
            if ($user->getLaunchs() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }
            $rarity = $this->getStr($randomRarity);
            //Calculs shiny
            $shinyTest = random_int(1, 200);
            if ($shinyTest == 1) {
                $isShiny = true;
            } else {
                $isShiny = false;
            }
            //On retire un lancer à l'utilisateur
            $this->getUser()->setLaunchs($this->getUser()->getLaunchs() - 1);
        } elseif ($pokeballId == 2) {
            //HYPER BALL------------------------

            if ($user->getHyperBall() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'
                ]);
            }

            //Calcul Rareté
            if ($randomRarity <= 70) {
                $rarity = 'TR';
                //70%
            } elseif ($randomRarity > 70 && $randomRarity <= 90) {
                $rarity = 'ME';
                //30%
            } else {
                $rarity = 'SR';
            }

            //Calculs shiny
            $shinyTest = random_int(1, 200);
            if ($shinyTest == 1) {
                $isShiny = true;
            } else {
                $isShiny = false;
            }

            //On retire un lancer à l'utilisateur
            $this->getUser()->setHyperBall($this->getUser()->getHyperBall() - 1);
        } elseif ($pokeballId == 3) {
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
            $this->getUser()->setShinyBall($this->getUser()->getShinyBall() - 1);
        } elseif ($pokeballId == 4) {
            //MASTER BALL --------------------------
            if ($user->getMasterBall() < 1) {
                return $this->json([
                    'error' => 'Vous n\'avez plus de lancers disponibles, veuillez réessayer plus tard !'

                ]);
            }

            if ($randomRarity <= 70) {
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
            $this->getUser()->setMasterBall($this->getUser()->getMasterBall() - 1);
        } else {
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
        foreach ($alreadyCapturedPokemon as $acp) {
            $pokeID[] = $acp->getPokeId();
        }
        $pokemonCapturedId = $pokemonCaptured->getPokemon()->getPokeId();

        if (in_array($pokemonCapturedId, $pokeID)) {
            $isNew = false;
        } else {
            $isNew = true;
        }

        if ($isNew || $isShiny) {
            //Hydratation BDD si le Pokémon est nouveau ou shiny

            $em = $doctrine->getManager();
            $em->persist($pokemonCaptured);
            $em->flush();
        } else {
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
            $this->getUser()->setMoney($this->getUser()->getMoney() + $rarityScale[$capturedRarity]);
        }

        //On compte un lancer en plus pour l'utilisateur
        $this->getUser()->setLaunchCount($this->getUser()->getLaunchCount() + 1);

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

    /**
     * @param float|int $randomRarity
     * @return string
     */
    public function getStr(float|int $randomRarity): string
    {
        if ($randomRarity < 40) {
            $rarity = 'C';
            //40%
        } elseif ($randomRarity < 70) {
            $rarity = 'PC';
            //30%
        } elseif ($randomRarity < 90) {
            $rarity = 'R';
            //20%
        } elseif ($randomRarity < 98) {
            $rarity = 'TR';
            //8%
        } elseif ($randomRarity < 99) {
            $rarity = 'ME';
            //1%
        } elseif ($randomRarity < 99.5) {
            $rarity = 'EX';
            //0.5%
        } elseif ($randomRarity < 100) {
            $rarity = 'SR';
            //0.5%
        } else {
            $rarity = 'UR';
            //0.01%
        }
        return $rarity;
    }

    #[Route('/pokedex-api/', name: 'app_pokedex_api')]
    #[IsGranted('ROLE_USER')]
    public function pokedexApi(Request $request, ManagerRegistry $doctrine): Response
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
                    'type1' => $pokemonToDisplay->getType(),
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

    #[Route('/capture-shop-api/', name: 'app_shop_api')]
    #[IsGranted('ROLE_USER')]
    public function shop(Request $request, ManagerRegistry $doctrine): Response
    {
        $itemRepo = $doctrine->getRepository(Items::class);
        $user = $this->getUser();
        $kartString = $request->get('quantityArray');
        $kart = explode(",", $kartString);
        $allItems = $itemRepo->findAll();
        $totalPrice = 0;

        //Comptage du panier

        foreach ($allItems as $item) {
            $unityPrice = $item->getPrice();
            $kartItemPrice = $unityPrice * (int)$kart[$item->getId() - 1];
            $totalPrice += $kartItemPrice;
        }

        $userWallet = $this->getUser()->getMoney();
        if ($userWallet < $totalPrice) {
            return $this->json([
                'error' => 'Vous n\'avez pas assez d\'argent pour acheter ce lot.',
            ]);
        }

        //On enlève l'argent de l'utilisateur
        $user->setMoney($user->getMoney() - $totalPrice);

        //Si l'utilisateur à assez d'argent
        $user->setHyperBall($user->getHyperBall() + (int)$kart[0]);
        $user->setShinyBall($user->getShinyBall() + (int)$kart[1]);
        $user->setMasterBall($user->getMasterBall() + (int)$kart[2]);
        $em = $doctrine->getManager();
        $em->flush();

        return $this->json([
            'success' => 'Votre achat a bien été effectué!',
            'kart' => $kart,
            'kartPrice' => $totalPrice,
        ]);
    }
}
