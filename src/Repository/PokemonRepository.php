<?php

namespace App\Repository;

use App\Entity\CapturedPokemon;
use App\Entity\Pokemon;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use function Symfony\Component\String\u;

/**
 * @extends ServiceEntityRepository<Pokemon>
 *
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

    public function save(Pokemon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Pokemon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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
            ->getOneOrNullResult()
        ;

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
            ->getOneOrNullResult()
        ;

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
            ->getOneOrNullResult()
        ;

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
            ->getOneOrNullResult()
        ;

    }





    public function getSpeciesEncounter(User $user): array
    {

        // SELECT *
        // FROM pokemon
        // INNER JOIN captured_pokemon ON
        // captured_pokemon.pokemon_id = pokemon.id
        // WHERE captured_pokemon.owner_id = cet utilisateur
        return $this->createQueryBuilder('p')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->where('cp.owner = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('p.pokeId', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getShinyCaptured(User $user): array
    {

        return $this->createQueryBuilder('p')
            ->select('DISTINCT p.pokeId')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->where('cp.owner = :userId')
            ->andWhere('cp.shiny = true')
            ->setParameter('userId', $user->getId())
            ->orderBy('p.pokeId', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getFullPokedexSize(): int
    {

        return $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

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
            ->getSingleScalarResult()
        ;

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
            ->getSingleScalarResult()
        ;

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
            ->getSingleScalarResult()
        ;

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
            ->getSingleScalarResult()
        ;

    }

}
