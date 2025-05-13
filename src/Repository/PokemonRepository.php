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

    public function getFullPokedexSize(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p)')
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

    /**
     * @return Pokemon[]
     */
    public function searchByName(string $name): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :name')
            ->setParameter('name', "%{$name}%")
            ->getQuery()
            ->getResult();
    }

    public function pokemonSeenByGen(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->select(
                'g.genRegion AS generation',
                'COUNT(DISTINCT p.pokeId) AS gen_captured',
                '(SELECT COUNT(pBase.id) FROM App\Entity\Pokemon pBase WHERE pBase.gen = g AND pBase.relateTo IS NULL) AS gen_total',
                '(SELECT COUNT(pAll.id) FROM App\Entity\Pokemon pAll WHERE pAll.gen = g) AS true_gen_total'
            )
            ->innerJoin('p.gen', 'g')
            ->where(
                'p.relateTo IS NULL AND (
                    EXISTS (
                        SELECT 1 FROM App\Entity\CapturedPokemon cp1
                        WHERE cp1.pokemon = p AND cp1.owner = :user
                    )
                    OR EXISTS (
                        SELECT 1 FROM App\Entity\CapturedPokemon cp2
                        JOIN cp2.pokemon pAlt2
                        WHERE pAlt2.relateTo = p AND cp2.owner = :user
                    )
                )
            '
            )
            ->groupBy('g.genRegion, g.genNumber, g.id')
            ->orderBy('g.genNumber')
            ->setParameter('user', $user)
            ->getQuery()
            ->getScalarResult();
    }

    public function pokemonSeenByGenTrue(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->select('g.genRegion as generation, COUNT(DISTINCT p.pokeId) AS true_gen_captured')
            ->innerJoin('p.capturedPokemon', 'cp')
            ->innerJoin('p.gen', 'g')
            ->where('cp.owner = :userId')
            ->groupBy('p.gen')
            ->orderBy('g.genNumber')
            ->setParameter('userId', $user)
            ->getQuery()
            ->getScalarResult();
    }

    public function getAllGenDex(User $user): array
    {
        $allGens = $this->pokemonSeenByGen($user);
        $allTrueGens = $this->pokemonSeenByGenTrue($user);

        for ($i = 0, $iMax = count($allGens); $i < $iMax; $i++) {
            $allGens[$i]['true_gen_captured'] = $allTrueGens[$i]['true_gen_captured'];
        }

        return $allGens;
    }
}
