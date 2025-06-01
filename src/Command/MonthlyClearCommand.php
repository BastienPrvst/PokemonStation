<?php

namespace App\Command;

use App\Repository\CapturedPokemonRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:monthly-clear',
    description: 'Add a short description for your command',
)]
class MonthlyClearCommand extends Command
{
    public function __construct(
        private readonly CapturedPokemonRepository $capturedPokemonRepository,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = new \DateTime('', new \DateTimeZone('Europe/Paris'));
        $startOfMonth = (clone $date)->modify('first day of this month')->setTime(0, 0, 0);
        $endOfMonth = (clone $date)->modify('last day of this month')->setTime(23, 59, 59);

        try {
            //Partie CP

            $query = $this->capturedPokemonRepository->createQueryBuilder('cp')
                ->delete()
                ->where('cp.captureDate NOT BETWEEN :start AND :end')
                ->andWhere('cp.timesCaptured < 0')
                ->setParameters([
                    'start' => $startOfMonth,
                    'end' => $endOfMonth,
                ])
                ->getQuery();

            $query->execute();

            //Partie User

            $query2 = $this->userRepository->createQueryBuilder('u')
                ->update()
                ->set('u.score', '0')
                ->getQuery();
            $query2->execute();

            $io->success('La commande a correctement fonctionnée.');
            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
