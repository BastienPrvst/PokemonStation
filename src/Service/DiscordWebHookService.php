<?php

namespace App\Service;

use App\Entity\CapturedPokemon;
use App\Entity\Pokemon;
use App\Entity\User;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordWebHookService
{
    private array $acceptedRarities = [
        "GMAX", "ME", "EX", "UR",
    ];

    private array $randomPhrase = [
        'Toujours les mêmes on en peut plus !',
        'La dingz !',
        'Bref...',
        'Ciao les loosers hehe',
        '#hacker',
        'Suffit d\'avoir du talent',
        'Son énorme crâne la.',
        'Cette personne possède un énorme talent.',
        'Cela semble si simple apprends nous !',
        'Je refuse d\' croire, tout simplement.',
        'Très salé ce Pokémon Johan',
    ];

    private array $colorPerRarities = [
        'EX' => 13435106,
        'UR' => 16038440,
        'GMAX' => 9502720,
        'ME' => 9502720,
    ];


    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function sendToDiscordWebHook(
        User $user,
        CapturedPokemon $capturedPokemon,
        bool $firstTimeShiny,
        bool $firstTimeNonShiny
    ): null|string {

        $randKey = array_rand($this->randomPhrase);
        /* @var $specie Pokemon */
        $specie = $capturedPokemon->getPokemon();

        if ($firstTimeNonShiny || $firstTimeShiny) {
            $timeSentence = '1ʳᵉ';
        } else {
            $timeSentence = $capturedPokemon->getTimesCaptured() . 'ᵉᵐᵉ';
        }

        if (
            $capturedPokemon->getShiny() === true || in_array($specie->getRarity(), $this->acceptedRarities, true)
        ) {
            $url =
                'https://pokemon-station.fr/medias/images/gifs/' .
                ($capturedPokemon->getShiny() ? 'shiny-' : '') .
                $specie->getNameEn() . '.gif';

            try {
                $embed = [
                    'title' => sprintf(
                        "**%s**%s a été libéré par %s !",
                        ucfirst($specie->getName()),
                        $capturedPokemon->getShiny() ? ' Shiny' : '',
                        $user->getPseudonym()
                    ),
                    'color' => $this->colorPerRarities[$specie->getRarity()],
                    'description' =>
                        "Libéré pour la $timeSentence fois !\n{$this->randomPhrase[$randKey]}\n\n" .
                        ($capturedPokemon->getShiny() ? ' (Attends, il est shiny ????)' : ''),
                    'image' => [
                        'url' => $url,
                    ],
                ];

                if ($capturedPokemon->getShiny() === true) {
                    $embed['thumbnail'] = [
                        'url' => "https://pokemon-station.fr/medias/images/sparkle/shiny-sparkle.gif"
                    ];
                }

                $this->httpClient->request('POST', $_ENV['DISCORD_WEBHOOK_URL'], [
                    'json' => [
                        'content' => null,
                        'embeds' => [ $embed ],
                    ],
                ]);

                return null;
            } catch (TransportExceptionInterface $e) {
                $discordError = $e->getMessage();
            }
            return $discordError;
        }

        return null;
    }
}
