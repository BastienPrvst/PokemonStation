<?php

namespace App\Command;

use App\Entity\Generation;
use App\Entity\Pokemon;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:add-gen',
    description: 'Incrémente tous les Pokemons d\'une génération dans la base de données.',
)]
class AddGenCommand extends Command
{
    private int $updates = 0;
    private int $skipped = 0;
    /** @see https://pokeapi.co/ */
    private string $apiUrl = 'https://pokeapi.co/api/v2';

    private array $skipArray = [];
    private array $ultraChimeres = [
        "nihilego", "buzzwole", "pheromosa", "xurkitree",
        "celesteela", "kartana", "guzzlord", "poipole",
        "naganadel", "stakataka", "blacephalon"
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('generation', InputArgument::REQUIRED, 'Le numéro de la génération à ajouter');
    }

    #[NoReturn] protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $gen = $input->getArgument('generation');
        $response = (new CurlHttpClient())->request('GET', "{$this->apiUrl}/generation/{$gen}");
        $json = json_decode($response->getContent());
        $varieties = [];
        $generation = new Generation();
        $generation->setGenNumber($gen);
        $generation->setGenRegion($json->main_region->name);
        $this->em->persist($generation);

        foreach ($json->pokemon_species as $pokemon) {
            $hasForm = false;
            $speciesResponse = (new CurlHttpClient())->request('GET', $pokemon->url);
            $speciesJson = json_decode($speciesResponse->getContent());
            $poke_id = $speciesJson->id;
            $pokemonResponse = (new CurlHttpClient())->request('GET', "{$this->apiUrl}/pokemon/{$poke_id}");
            $pokemonJson = json_decode($pokemonResponse->getContent());
            $pokemon = $this->generatePokemon($speciesJson, $pokemonJson, $generation);

            if (count($speciesJson->varieties) > 1) {
                $varieties[$poke_id] = [
                    'filtered_varieties' => array_filter(
                        $speciesJson->varieties,
                        fn($var) => !$var->is_default
                    ),
                    'is_legendary' => $speciesJson->is_legendary,
                    'is_mythical' => $speciesJson->is_mythical,
                    'capture_rate' => $speciesJson->capture_rate,
                    'name' => $this->getFrenchName($speciesJson),
                    'speciesJson' => $speciesJson,
                ];

                $hasForm = true;
            }

            if ($pokemon !== false) {
                $this->em->persist($pokemon);
                if ($hasForm) {
                    $this->em->flush();
                }
            }
        }
        //Formes alternatives
        foreach ($varieties as $variety) {
            foreach ($variety['filtered_varieties'] as $filtered_variety) {
                $name = $filtered_variety->pokemon->name;

                try {
                    $httpClient = new CurlHttpClient();

                    $pokemonResponse = $httpClient->request('GET', $filtered_variety->pokemon->url);
                    if ($pokemonResponse->getStatusCode() !== 200) {
                        error_log("Erreur HTTP sur {$filtered_variety->pokemon->url}");
                        continue;
                    }

                    $pokemonFormResponse = $httpClient->request('GET', "{$this->apiUrl}/pokemon-form/{$name}");
                    if ($pokemonFormResponse->getStatusCode() !== 200) {
                        error_log("Erreur HTTP sur {$this->apiUrl}/pokemon-form/{$name}");
                        continue;
                    }

                    $pokemonJson = json_decode($pokemonResponse->getContent(), false, 512, JSON_THROW_ON_ERROR);
                    $pokemonFormJson = json_decode($pokemonFormResponse->getContent(), false, 512, JSON_THROW_ON_ERROR);
                } catch (
                    ClientExceptionInterface |
                    ServerExceptionInterface |
                    \JsonException |
                    TransportExceptionInterface $e
                ) {
                    error_log($e->getMessage());
                    $this->skipArray[] = $name;
                    continue;
                }

                $speciesJson = $variety['speciesJson'];
                $isLegendary = $variety['is_legendary'];
                $isMythical = $variety['is_mythical'];
                $isMega = $pokemonFormJson->is_mega;
                $pokemon = $this->generatePokemon($speciesJson, $pokemonJson, $generation, $pokemonFormJson);
                $rarity = null;

                if ($pokemon !== false) {
                    if ($isLegendary || $isMythical) {
                        $rarity = 'UR';
                    } elseif ($isMega === true) {
                        $rarity = 'ME';
                    } elseif ($pokemonFormJson->form_name === 'gmax') {
                        $rarity = 'GMAX';
                    }

                    if ($rarity !== null) {
                        $pokemon->setRarity($rarity);
                    }

                    $this->em->persist($pokemon);
                }
            }
        }

        $this->em->flush();
        $io->success("Vous avez ajouté {$this->updates} Pokemons de la #{$gen} génération.");
        if ($this->skipped > 0) {
            $str = implode(',', $this->skipArray);
            $io->info(sprintf(" %d Pokémon(s) n'avaient pas de gif et ont donc été skip. \n %s", $this->skipped, $str));
        }

        return Command::SUCCESS;
    }

    private function generatePokemon(
        \stdClass $speciesJson,
        \stdClass $pokemonJson,
        Generation $generation,
        ?\stdClass $pokemonFormJson = null,
    ): Pokemon|false {

        if ($this->generateGifs($pokemonJson)) {
            $type1_en = isset($pokemonJson->types[0]) ? $pokemonJson->types[0]->type->name : null;
            $type2_en = isset($pokemonJson->types[1]) ? $pokemonJson->types[1]->type->name : null;
            $type1_translated = $this->translator->trans($type1_en, domain: 'types');
            $type2_translated = $this->translator->trans($type2_en, domain: 'types');

            $type1 = $type1_translated ? $this->removeAccents($type1_translated) : null;
            $type2 = $type2_translated ? $this->removeAccents($type2_translated) : null;
            $this->generateCry($pokemonJson);

            $pokemon = (new Pokemon())
                ->setName($this->getFrenchName($speciesJson))
                ->setType($type1)
                ->setType2($type2)
                ->setDescription($this->getFrenchDescription($speciesJson))
                ->setNameEn($pokemonJson->name)
                ->setPokeId($pokemonJson->id)
                ->setGen($generation);

            if (
                $speciesJson->is_legendary === true
                || $speciesJson->is_mythical === true
                || in_array(($pokemonJson->name), $this->ultraChimeres, true)
            ) {
                $pokemon->setRarity('EX');
            } else {
                $pokemon->setRarity($this->defineRarity($pokemonJson->stats, $speciesJson->capture_rate));
            }

            $this->em->persist($pokemon);

            //Si forme spéciale
            if ($pokemonFormJson !== null) {
                $pokemonToRelate = $this->em->getRepository(Pokemon::class)->findOneBy(['pokeId' => $speciesJson->id]);
                $pokemon->setRelateTo($pokemonToRelate);
                $pokemon->setName(strtolower($pokemonFormJson->names[0]->name));
            }

            $this->updates++;
            return $pokemon;
        }

        $this->skipped++;
        $this->skipArray[] = $pokemonJson->name;
        return false;
    }
    private function getFrenchName(\stdClass $speciesJson): string
    {
        $names = array_filter(
            $speciesJson->names,
            static fn($name) => $name->language->name === 'fr'
        );
        $name = current($names)?->name ?? '';
        return strtolower($name);
    }

    private function getFrenchDescription(\stdClass $speciesJson): string
    {
        $descriptions = array_filter(
            $speciesJson->flavor_text_entries,
            fn($flavor) => $flavor->language->name === 'fr'
        );
        $description = current($descriptions)?->flavor_text ?? '';
        return preg_replace('/\s+/', ' ', trim($description));
    }

    private function defineRarity(array $arrayStats, int $captureRate, bool $ex = false): string
    {
        if ($ex === true) {
            return 'EX';
        }

        $score = 0;
        $totalStats = 0;
        foreach ($arrayStats as $stat) {
            $totalStats += $stat->base_stat;
        }

        if ($totalStats < 400) {
            $score += 1;
        } elseif ($totalStats < 500) {
            $score += 2;
        } elseif ($totalStats < 600) {
            $score += 4;
        } elseif ($totalStats < 700) {
            $score += 5;
        }

        if ($captureRate > 150) {
            $score += 1;
        } elseif ($captureRate > 100) {
            $score += 2;
        } elseif ($captureRate > 50) {
            $score += 3;
        } elseif ($captureRate > 30) {
            $score += 5;
        }

        if ($score < 3) {
            return 'C';
        }
        if ($score < 6) {
            return 'PC';
        }
        if ($score < 9) {
            return 'R';
        }
        return 'TR';
    }

    private function generateGifs(\stdClass $pokemonJson): bool
    {
        $gifUrl = $pokemonJson->sprites->other->showdown?->front_default;
        $gifShinyUrl = $pokemonJson->sprites->other->showdown?->front_shiny;
        if ($gifUrl) {
            $gifResponse = (new CurlHttpClient())->request('GET', $gifUrl);
            $gifShinyResponse = (new CurlHttpClient())->request('GET', $gifShinyUrl);
            $filePath = $this->kernel->getProjectDir() . "/public/medias/images/gifs/{$pokemonJson->name}.gif";
            $filePathShiny = $this->kernel->getProjectDir() . "/public/medias/images/gifs/shiny-{$pokemonJson->name}.gif";

            file_put_contents($filePath, $gifResponse->getContent());
            file_put_contents($filePathShiny, $gifShinyResponse->getContent());

            return true;
        }
        return false;
    }

    private function generateCry(\stdClass $pokemonJson): void
    {
        $cryUrl = $pokemonJson->cries->latest;
        if ($cryUrl) {
            $cryResponse = (new CurlHttpClient())->request('GET', $cryUrl);
            $filePath = $this->kernel->getProjectDir() . "/public/medias/cries/{$pokemonJson->name}-cry.mp3";
            file_put_contents($filePath, $cryResponse->getContent());
        }
    }

    private function removeAccents(string $str): string
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
    }
}
