<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:set-retro-money',
    description: '',
)]
class SetRetroMoneyCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Définition des échelles de rareté (valeur par capture)
        $rarityScale = [
            'C' => 1,
            'PC' => 3,
            'R' => 5,
            'TR' => 10,
            'ME' => 50,
            'GMAX' => 50,
            'SR' => 100,
            'EX' => 100,
            'UR' => 250,
        ];

        // Paliers de multiplicateurs
        $multipliers = [
            5 => 5,
            10 => 10,
            20 => 20,
            50 => 50,
            100 => 100,
            1000 => 1000,
        ];

        // Connexion à la base de données
        $conn = $this->entityManager->getConnection();

        // Requête SQL pour récupérer les captures par utilisateur et Pokémon
        $sql = <<<SQL
        SELECT cp.owner_id, p.rarity, cp.pokemon_id, SUM(cp.times_captured) AS total
        FROM captured_pokemon cp
        INNER JOIN pokemon p ON cp.pokemon_id = p.id
        GROUP BY cp.owner_id, p.rarity, cp.pokemon_id
    SQL;

        // Récupération des résultats de la requête
        $results = $conn->fetchAllAssociative($sql);

        // Tableau pour stocker les gains par utilisateur
        $userEarnings = [];

        // Calcul des pièces pour chaque utilisateur
        foreach ($results as $row) {
            $userId = $row['owner_id'];
            $pokemonId = $row['pokemon_id'];
            $rarity = $row['rarity'];
            $count = (int)$row['total'];

            // Valeur par capture selon la rareté
            $valuePerCapture = $rarityScale[$rarity] ?? 0;

            // Calcul du montant total à attribuer en fonction des multiplicateurs
            $totalMoney = 0;
            foreach ($multipliers as $threshold => $multiplier) {
                if ($count >= $threshold) {
                    // On ajoute des pièces en fonction du multiplicateur et de la rareté
                    $totalMoney += $multiplier * $valuePerCapture;
                }
            }

            // On met à jour les gains pour cet utilisateur
            $userEarnings[$userId] = ($userEarnings[$userId] ?? 0) + $totalMoney;
        }

        // Mise à jour des utilisateurs avec les pièces calculées
        $userRepo = $this->entityManager->getRepository(User::class);
        foreach ($userEarnings as $userId => $money) {
            $user = $userRepo->find($userId);
            if ($user) {
                $user->setMoney($user->getMoney() + $money);
            }
        }

        // Enregistrement des mises à jour en base de données
        $this->entityManager->flush();

        // Affichage du message de succès
        $output->writeln('Mise à jour des pièces terminée.');

        return Command::SUCCESS;
    }
}
