<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }
        $user->setPassword($newHashedPassword);
    }

    public function top10TotalSpeciesSeen(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u AS user, COUNT(DISTINCT cp.pokemon) AS total_species_seen, u.launch_count AS launch_count')
            ->innerJoin('u.capturedPokemon', 'cp')
            ->innerJoin('cp.pokemon', 'p')
            ->groupBy('u')
            ->orderBy('total_species_seen', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function topMonthlyShinies(): array
    {
        $firstDay = new \DateTime('first day of this month');
        $firstDay->setTime(0, 0, 0);
        $lastDay = (clone $firstDay)->modify('+1 month');

        return $this->createQueryBuilder('u')
            ->select('u as user, COUNT(DISTINCT cp) AS monthly_shinies')
            ->innerJoin('u.capturedPokemon', 'cp')
            ->innerJoin('cp.pokemon', 'p')
            ->where('cp.shiny = 1')
            ->andWhere('cp.captureDate BETWEEN :firstDay AND :lastDay')
            ->setParameters([
                'firstDay' => $firstDay,
                'lastDay' => $lastDay,
            ])
            ->groupBy('u')
            ->orderBy('monthly_shinies', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function topMonthlyRarity(): array
    {
        $firstDay = new \DateTime('first day of this month');
        $firstDay->setTime(0, 0, 0);
        $lastDay = (clone $firstDay)->modify('+1 month');

        return $this->createQueryBuilder('u')
            ->select('SUM(
                CASE p.rarity 
                    WHEN \'TR\' THEN 10
                    WHEN \'GMAX\' THEN 50
                    WHEN \'ME\' THEN 50
                    WHEN \'SR\' THEN 100
                    WHEN \'EX\' THEN 100
                    WHEN \'UR\' THEN 250
                    ELSE 0 
                END
            ) as total_points, u as user')
            ->innerJoin('u.capturedPokemon', 'cp')
            ->innerJoin('cp.pokemon', 'p')
            ->where('cp.captureDate BETWEEN :firstDay AND :lastDay')
            ->setParameters([
                'firstDay' => $firstDay,
                'lastDay' => $lastDay,
            ])
            ->groupBy('u')
            ->orderBy('total_points', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function totalPokemon(): int|null
    {
        return $this->createQueryBuilder('u')
            ->select('SUM(u.launch_count) as total_pokemon_captured')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
