<?php

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pokemon[]    findAll()
 * @method Pokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

    public function findPrev(Pokemon $currentPokemon, int $offset = 0): ?Pokemon
    {
        // SELECT * FROM `pokemon` WHERE id < xxx ORDER BY id DESC LIMIT 1
        return $this->createQueryBuilder('p')
            ->andWhere('p.pokeId < ' . $currentPokemon->getPokeId())
            ->orderBy('p.pokeId', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNext(Pokemon $currentPokemon, int $offset = 0): ?Pokemon
    {
        // SELECT * FROM `pokemon` WHERE id > xxx ORDER BY id LIMIT 1
        return $this->createQueryBuilder('p')
            ->andWhere('p.pokeId > ' . $currentPokemon->getPokeId())
            ->orderBy('p.pokeId')
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextSpecieEncounter(Pokemon $currentPokemon, User $user): ?Pokemon
    {
        // SELECT * FROM pokemon INNER JOIN captured_pokemon ON pokemon.id = captured_pokemon.pokemon_id ORDER BY pokemon.id ASC
        return $this->createQueryBuilder('p')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->where('cp.owner = :userId')
            ->andWhere('p.pokeId > :pokeId ')
            ->setParameter('userId', $user->getId())
            ->setParameter('pokeId', $currentPokemon->getPokeId())
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPreviousSpecieEncounter(Pokemon $currentPokemon, User $user): ?Pokemon
    {
        // SELECT * FROM pokemon INNER JOIN captured_pokemon ON pokemon.id = captured_pokemon.pokemon_id ORDER BY pokemon.id DESC
        return $this->createQueryBuilder('p')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->where('cp.owner = :userId')
            ->andWhere('p.pokeId < :pokeId ')
            ->setParameter('userId', $user->getId())
            ->setParameter('pokeId', $currentPokemon->getPokeId())
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getSpeciesEncounter(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->where('cp.owner = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('p.pokeId', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getShinySpeciesEncounter(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->where('cp.owner = :userId')
            ->andWhere('cp.shiny = TRUE')
            ->setParameter('userId', $user->getId())
            ->orderBy('p.pokeId', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getFullPokedexSize(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountEncounteredBy(User $user): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->innerJoin('cp.owner', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountUniqueEncounteredBy(User $user): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p)')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->innerJoin('cp.owner', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountShiniesEncounteredBy(User $user): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->innerJoin('cp.owner', 'u')
            ->where('u.id = :userId')
            ->andWhere('cp.shiny = 1')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountByRarityEncounteredBy(User $user, string $rarity): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->innerJoin('cp.owner', 'u')
            ->where('u.id = :userId')
            ->andWhere('p.rarity = :rarity')
            ->setParameter('userId', $user->getId())
            ->setParameter('rarity', $rarity)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByRarityAndType(string $rarity, string $type): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.rarity = :rarity')
            ->andWhere('(p.type = :type OR p.type2 = :type)')
            ->setParameter('rarity', $rarity)
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
}
