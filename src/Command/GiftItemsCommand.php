<?php

namespace App\Command;

use App\Entity\Items;
use App\Entity\User;
use App\Entity\UserItems;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:gift-items',
    description: 'Permet de donner des items à tous les joueurs.',
)]
class GiftItemsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('Quantité', InputArgument::REQUIRED, 'Quantité d\'objets donnés.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('Quantité');
        $itemsRepo = $this->entityManager->getRepository(Items::class);
        $userRepo = $this->entityManager->getRepository(User::class);

        $itemNames = [];
        $allItems = $itemsRepo->findAll();

        if (empty($allItems)) {
            $io->error('Aucun item dans la base de données.');
            return Command::FAILURE;
        }

        foreach ($allItems as $item) {
            $itemNames[] = ucfirst($item->getName());
        }

        $question = new ChoiceQuestion(
            sprintf('Quel objet voulez-vous donner en %s exemplaire(s) ?', $arg1),
            $itemNames
        );

        $question->setErrorMessage('Une erreur est survenue');

        $itemSelected = $io->askQuestion($question);

        try {
            $item = $itemsRepo->findOneBy(['name' => $itemSelected]);
            $allUsers = $userRepo->findAll();

            if (empty($allUsers)) {
                $io->error('Aucun utilisateurs trouvés.');
                return Command::FAILURE;
            }

            foreach ($allUsers as $user) {
                $userItem = new UserItems();
                $userItem->setItem($item);
                $userItem->setUser($user);
                $userItem->setQuantity($arg1);
                $this->entityManager->persist($userItem);
            }

            $this->entityManager->flush();
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        $io->success('Les objets ont bien étés donnés aux utilisateurs.');
        return Command::SUCCESS;
    }
}
