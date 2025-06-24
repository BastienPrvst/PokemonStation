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
        $query = $this->createQueryBuilder('u')
            ->select('u AS user, COUNT(DISTINCT cp.pokemon) AS total_species_seen, u.launch_count AS launch_count')
            ->innerJoin('u.capturedPokemon', 'cp')
            ->innerJoin('cp.pokemon', 'p')
            ->groupBy('u')
            ->orderBy('total_species_seen', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->rankTop($query, 'total_species_seen');
    }

    public function topMonthlyShinies(): array
    {
        $firstDay = new \DateTime('first day of this month');
        $firstDay->setTime(0, 0, 0);
        $lastDay = (clone $firstDay)->modify('+1 month');

        $query = $this->createQueryBuilder('u')
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

        return $this->rankTop($query, 'monthly_shinies');
    }

    public function topMonthlyRarity(): array
    {
        $query = $this->createQueryBuilder('u')
            ->select('u as user, u.score as total_points')
            ->groupBy('u')
            ->orderBy('total_points', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->rankTop($query, 'total_points');
    }

    /**
     * @param array $top
     * @param string $field
     * @return array
     */
    private function rankTop(array $top, string $field): array
    {
        $rank = 1;
        $i = 1;
        $lastValue = null;

        foreach ($top as &$user) {
            if ($user[$field] !== $lastValue) {
                $rank = $i;
            }
            $user['rank'] = $rank;
            $lastValue = $user[$field];
            $i++;
        }
        unset($user);

        return $top;
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
