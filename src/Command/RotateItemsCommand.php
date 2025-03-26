<?php

namespace App\Command;

use App\Entity\Items;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:rotate-items',
    description: 'Set 5 randoms items to active',
)]
class RotateItemsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $itemsRepo = $this->entityManager->getRepository(Items::class);
            $activeItems = $itemsRepo->findBy(['active' => true]);
            foreach ($activeItems as $item) {
                $item->setActive(false);
                $this->entityManager->persist($item);
            }
            $this->entityManager->flush();

            $itemIds = $itemsRepo->createQueryBuilder('i')
                ->select('i.id')
                ->getQuery()
                ->getResult();

            $itemIds = array_column($itemIds, 'id');
            $numberToChange = [];
            for ($i = 0; $i < 5; $i++){
                do {
                    $rand = $itemIds[array_rand($itemIds)];
                } while (in_array($rand, $numberToChange, true));

                $numberToChange[] = $rand;
            }

            $query = $itemsRepo->createQueryBuilder('i')
                ->update()
                ->set('i.active', ':active')
                ->where('i.id IN (:ids)')
                ->setParameter('active', true)
                ->setParameter('ids', $numberToChange)
                ->getQuery();

            $query->execute();
            $io->success('Commande effectuée avec succès');
        }catch (\Exception $exception){
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
