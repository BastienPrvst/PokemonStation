<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function top10TotalPokemonFreed(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u, COUNT(cp.pokemon) total_pokemon_captured')
            ->innerJoin('u.capturedPokemon', 'cp')
            ->groupBy('u.id')
            ->orderBy('total_pokemon_captured', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }


}
