<?php

namespace App\Command;

use App\Entity\Pokemon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:add-gen',
    description: 'Incrémente tous les Pokemons d\'une génération dans la base de données.',
)]
class AddGenCommand extends Command
{

    private int $updates = 0;
    /** @see https://pokeapi.co/ */
    private string $apiUrl = 'https://pokeapi.co/api/v2';

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $gen = $input->getArgument('generation');

        // Récupère la liste de tous les pokemons de la génération
        $response = (new CurlHttpClient)->request('GET', "{$this->apiUrl}/generation/{$gen}");
        $json = json_decode($response->getContent());
        $varieties = [];
        $forms = [];

        foreach ($json->pokemon_species as $pokemon) {

            $name_en = $pokemon->name;
            $poke_id = explode('/', $pokemon->url)[6];

            // Récupère les 2 fiches d'informations détaillées pour un pokemon
            $speciesResponse = (new CurlHttpClient)->request('GET', $pokemon->url);
            $pokemonResponse = (new CurlHttpClient)->request('GET', "{$this->apiUrl}/pokemon/{$poke_id}");
            $speciesJson = json_decode($speciesResponse->getContent());
            $pokemonJson = json_decode($pokemonResponse->getContent());

            $type1_en = isset($pokemonJson->types[0]) ? $pokemonJson->types[0]->type->name : null;
            $type2_en = isset($pokemonJson->types[1]) ? $pokemonJson->types[1]->type->name : null;
            $type1 = $this->translator->trans($type1_en, domain: 'types') ?: null;
            $type2 = $this->translator->trans($type2_en, domain: 'types') ?: null;

            $descriptions = array_filter(
                $speciesJson->flavor_text_entries,
                fn($flavor) => $flavor->language->name === 'fr'
            );
            $description = current($descriptions)?->flavor_text ?? '';
            $description = preg_replace('/\s+/', ' ', trim($description));

            $names = array_filter(
                $speciesJson->names,
                fn($name) => $name->language->name === 'fr'
            );
            $name = current($names)?->name ?? '';
            $name = strtolower($name);

            $gifUrl = $pokemonJson->sprites->other->showdown?->front_default;
            $gifShinyUrl = $pokemonJson->sprites->other->showdown?->front_shiny;

            $megas = array_filter(
                $speciesJson->varieties,
                function($var) {
                    return str_contains($var->pokemon->name, '-primal') ||
                    str_contains($var->pokemon->name, '-mega') ||
                    str_contains($var->pokemon->name, '-gmax');
                }
            );

            // Stock les formes alternatives
            if (count($speciesJson->varieties) > 1) {
                $varieties[$poke_id] = array_filter(
                    $speciesJson->varieties,
                    fn($var) => !$var->is_default
                );
            }

            // Stock les formes spéciales
            if (count($pokemonJson->forms) > 1) {
                $forms[$poke_id] = $pokemonJson->forms;
            }

            // Détermine le niveau de rareté 
            if ($speciesJson->is_legendary || $speciesJson->is_mythical) {
                $rarity = 'EX';
            } else if ($speciesJson->varieties) {

            } else {
                $rarity = 'C';
            }

            // Récupère les .gif default et shiny
            if ($gifUrl) {
                $gifResponse = (new CurlHttpClient)->request('GET', $gifUrl);
                $gifShinyResponse = (new CurlHttpClient)->request('GET', $gifShinyUrl);
                $filePath = $this->kernel->getProjectDir() . "/public/images/gifs/{$name_en}.gif";
                $filePathShiny = $this->kernel->getProjectDir() . "/public/images/gifs/shiny-{$name_en}.gif";

                file_put_contents($filePath, $gifResponse->getContent());
                file_put_contents($filePathShiny, $gifShinyResponse->getContent());
            }

            $pokemon = (new Pokemon)
                ->setName($name)
                ->setType($type1)
                ->setType2($type2)
                ->setDescription($description)
                ->setGif("{$name_en}.gif")
                ->setNameEn($name_en)
                ->setRarity($rarity)
                ->setPokeId($poke_id);

            $this->em->persist($pokemon);
            $this->updates++;

            // Temporiser 1/3 seconde entre chaque Pokemon pour ne pas spam l'API
            usleep(333333);
        }

        foreach ($varieties as $poke_id => $variety) {
            // TODO: récupérer les valeurs depuis /pokemon
        }

        foreach ($forms as $poke_id => $form) {
            // TODO: récupérer les valeurs depuis /pokemon
        }

        $this->em->flush();

        $io->success("Vous avez ajouté {$this->updates} Pokemons de la #{$gen} génération.");

        return Command::SUCCESS;
    }
}
