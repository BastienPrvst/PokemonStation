<?php

namespace App\Command;

use App\Entity\Items;
use Doctrine\ORM\EntityManagerInterface;
use Imagick;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:get-items',
    description: 'Ajoute toutes les images d\'objets d\'une catégorie de l\'API Pokemon ',
)]
class GetItemsCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly EntityManagerInterface $em,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $curl = new CurlHttpClient();
        $errors = [];

        $objectsResponse = $curl->request('GET', 'https://pokeapi.co/api/v2/item-category/?limit=200');
        if ($objectsResponse->getStatusCode() !== 200) {
            $io->error('Impossible de communiquer avec l\'API.' . $objectsResponse->getStatusCode());
            return Command::FAILURE;
        }

        $categories = json_decode($objectsResponse->getContent());
        $categoryList = [];

        foreach ($categories->results as $category) {
            $categoryList[] = ucfirst($category->name);
        }

            $question = new ChoiceQuestion('Quelle catégorie d\'objets voulez vous récuperer ? \n', $categoryList);
            $question->setErrorMessage('Cette catégorie n\'est pas valide');
            $categorieChoose = $this->getHelper('question')->ask($input, $output, $question);

            $result =
                array_filter($categories->results, fn($category) =>
                    ucfirst($category->name) === $categorieChoose);

            $url = end($result);

        foreach ($categories->results as $category) {
            if (ucfirst($category->name) === $categorieChoose) {
                $url = $category->url;
                break;
            }
        }

        try {
            $categoryResponse = $curl->request('GET', $url);
            $category = json_decode($categoryResponse->getContent());

            foreach ($category->items as $item) {
                try {
                    $object = $curl->request('GET', $item->url);
                    $object = json_decode($object->getContent());
                    $objectUrl = $object->sprites->default;

                    if ($objectUrl === null) {
                        continue;
                    }

                    if (str_contains($category->name, 'ball')) {
                        $directory =
                        $this->kernel->getProjectDir() . '/public/medias/images/balls/' . $object->name . '.png';

                        $newItem =  new Items();
                        $newItem->setName($object->name);
                        $newItem->setImage($object->name . '.png');
                        $newItem->setPrice(1000);
                        $newItem->setStats($this->createStats());
                        $this->em->persist($newItem);
                    } else {
                        $directory =
                        $this->kernel->getProjectDir() . '/public/medias/images/items/' . $object->name . '.png';
                    }
                    file_put_contents($directory, file_get_contents($objectUrl));

                    $imagick = new Imagick($directory);
                    $imagick->scaleImage(150, 150, Imagick::FILTER_LANCZOS, 1);
                    $imagick->cropImage(100, 100, 25, 25);
                    $imagick->modulateImage(100, 150, 100);
                    $imagick->contrastImage(1);
                    $imagick->writeImage($directory);
                } catch (\Exception) {
                    $errors[] = $item->name;
                    continue;
                }
            }
        } catch (\Exception $e) {
            $io->error('Impossible de communiquer avec l\'API.' . $e->getMessage());
            return Command::FAILURE;
        }


        $io->success('Les objets ont bien été téléchargés.');
        if (count($errors) > 0) {
            $str = implode(',', $errors);
            $io->info(sprintf(" %d objets ont étés ignorés  \n %s", count($errors), $str));
        }

        $this->em->flush();
        return Command::SUCCESS;
    }

    private function createStats(): array
    {
        $types = [
            'eau', 'feu', 'plante', 'insecte', 'roche', 'sol', 'glace', 'acier',
            'dragon', 'combat', 'tenebres', 'psy', 'vol', 'fee', 'poison',
            'electrik', 'normal', 'spectre'
        ];

        $typePercentage = 100 / count($types);
        $typeStats = array_fill_keys($types, $typePercentage);

        $rarityStats = [
            'C' => 40,
            'PC' => 30,
            'R' => 20,
            'TR' => 7,
            'ME' => 1,
            'GMAX' => 1,
            'SR' => 0.7,
            'EX' => 0.3,
            'UR' => 0.1,

        ];

        $shiny = 0.5;

        return [
            'type' => $typeStats,
            'rarity' => $rarityStats,
            'shiny' => $shiny,
        ];
    }
}
