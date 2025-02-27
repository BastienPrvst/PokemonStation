<?php

namespace App\Command;

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
    name: 'app:add-items',
    description: 'Ajoute toutes les images d\'objets d\'une catégorie de l\'API Pokemon ',
)]
class GetItemsCommand extends Command
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel, string $name = null)
    {
        parent::__construct($name);
        $this->kernel = $kernel;
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

        $objectsResponse = $curl->request('GET', 'https://pokeapi.co/api/v2/item-category/?limit=2000');
        if ($objectsResponse->getStatusCode() !== 200) {
            $io->error('Impossible de communiquer avec l\'API.');
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
        } catch (\Exception) {
            $io->error('Impossible de communiquer avec l\'API.');
            return Command::FAILURE;
        }

            $io->success('Les objets ont bien été téléchargés.');
        if (count($errors) > 0) {
            $str = implode(',', $errors);
            $io->info(sprintf(" %d objets ont étés ignorés  \n %s", count($errors), $str));
        }

        return Command::SUCCESS;
    }
}
