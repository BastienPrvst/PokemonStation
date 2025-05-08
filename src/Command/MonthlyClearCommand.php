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
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');

        try {
            //Partie CP

            $query = $this->capturedPokemonRepository->createQueryBuilder('cp')
                ->delete()
                ->where('NOT (MONTH(cp.capture_date) = :month AND YEAR(cp.capture_date) = :year)')
                ->andWhere('cp.times_captured < 0')
                ->setParameters([
                    'month' => $month,
                    'year' => $year,
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
