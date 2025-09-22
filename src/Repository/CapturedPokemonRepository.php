<?php

namespace App\Repository;

use App\Entity\CapturedPokemon;
use App\Entity\Pokemon;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<CapturedPokemon>
 *
 * @method CapturedPokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method CapturedPokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method CapturedPokemon[]    findAll()
 * @method CapturedPokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CapturedPokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CapturedPokemon::class);
    }

    public function save(CapturedPokemon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CapturedPokemon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSpeciesCaptured(User $user): array
    {
        $result = $this->createQueryBuilder('cp')
            ->select('DISTINCT p.pokeId AS pokeId')
            ->innerJoin('cp.pokemon', 'p')
            ->where('cp.owner = :userId')
            ->andWhere('cp.shiny = false')
            ->setParameter(':userId', $user->getId())
            ->orderBy('p.pokeId', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'pokeId');
    }

    public function findShinyCaptured(User $user): array
    {
        $result = $this->createQueryBuilder('cp')
            ->select('DISTINCT p.pokeId AS pokeId')
            ->innerJoin('cp.pokemon', 'p')
            ->where('cp.owner = :userId')
            ->andWhere('cp.shiny = true')
            ->setParameter('userId', $user->getId())
            ->orderBy('p.pokeId', 'ASC')
            ->getQuery()
            ->getResult()
            ;
        return array_column($result, 'pokeId');
    }

    public function getLastRareCaptured(): array
    {
        return $this->createQueryBuilder('cp')
            ->innerJoin('cp.pokemon', 'p')
            ->innerJoin('cp.owner', 'u')
            ->where('p.rarity IN (:rarities)')
            ->orWhere('cp.shiny = true AND cp.timesCaptured > 0')
            ->setParameter('rarities', ['UR', 'EX'])
            ->orderBy('cp.captureDate', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param  User $user
     * @param  Pokemon[] $pokemons
     * @return CapturedPokemon[]
     */
    public function findSpeciesCapturedByPokemon(User $user, array $pokemons): array
    {
		$totalResult = [];
        $result = $this->createQueryBuilder('cp')
            ->select('DISTINCT p.pokeId AS pokeId, cp.quantity')
            ->innerJoin('cp.pokemon', 'p')
            ->where('cp.owner = :user')
            ->andWhere('cp.shiny = false')
            ->andWhere('p IN(:pokemons)')
            ->setParameters([
                'user' => $user,
                'pokemons' => $pokemons,
            ])
            ->orderBy('p.pokeId', 'ASC')
            ->getQuery()
            ->getScalarResult();

        $totalResult['pokeIds'] = array_column($result, 'pokeId');
		$totalResult['quantities'] = array_column($result, 'quantity', 'pokeId');

		return $totalResult;
    }

    /**
     * @param  User $user
     * @param  Pokemon[] $pokemons
     * @return CapturedPokemon[]
     */
    public function findShinyCapturedByPokemon(User $user, array $pokemons): array
    {
	    $totalResult = [];
        $result = $this->createQueryBuilder('cp')
            ->select('DISTINCT p.pokeId AS pokeId, cp.quantity')
            ->innerJoin('cp.pokemon', 'p')
            ->where('cp.owner = :user')
            ->andWhere('cp.shiny = true')
            ->andWhere('p IN(:pokemons)')
            ->setParameters([
                'user' => $user,
                'pokemons' => $pokemons,
            ])
            ->orderBy('p.pokeId', 'ASC')
            ->getQuery()
            ->getScalarResult();

	    $totalResult['pokeIds'] = array_column($result, 'pokeId');
	    $totalResult['quantities'] = array_column($result, 'quantity', 'pokeId');

		return $totalResult;
    }

    public function findOnePokemon(User $user, bool $shiny, Pokemon $pokemon)
    {
        return $this->createQueryBuilder('cp')
            ->where('cp.owner = :user')
            ->andWhere('cp.shiny = :shiny')
            ->andWhere('cp.pokemon = :pokemon')
            ->andWhere('cp.timesCaptured >= 1')
            ->setParameters([
                'user' => $user,
                'shiny' => $shiny,
                'pokemon' => $pokemon,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countUserCapturedPokemon(User $user): array
    {
        return $this->createQueryBuilder('cp')
            ->select('p.rarity AS rarity,
             COUNT(DISTINCT cp.id) AS total_unique,
              SUM(cp.timesCaptured) AS total_captured')
            ->innerJoin('cp.pokemon', 'p')
            ->where('cp.owner = :user')
            ->groupBy('p.rarity')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function countUserShinies(User $user): array
    {
        return $this->createQueryBuilder('cp')
            ->select('COUNT(DISTINCT cp.id) AS total_unique, SUM(cp.timesCaptured) AS total_captured')
            ->where('cp.owner = :user')
            ->andWhere('cp.shiny = true')
            ->setParameter(':user', $user)
            ->getQuery()
            ->getResult();
    }

    public function countDistinctUserCapturedPokemon(User $user): int
    {
        return $this->createQueryBuilder('cp')
            ->select('COUNT(DISTINCT p.pokeId)')
            ->innerJoin('cp.pokemon', 'p')
            ->where('cp.owner = :user')
            ->setParameter(':user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

	public function findTradeable(User $user): array
	{
		return $this->createQueryBuilder('cp')
			->where('cp.owner = :user AND cp.quantity > 1')
			->orWhere('cp.owner = :user AND cp.shiny = true AND cp.quantity > 0')
			->setParameter('user', $user)
			->getQuery()
			->getResult();
	}

	public function findInteressting(User $user, User $connectedUser)
	{
		return $this->createQueryBuilder('cp')
			->where('cp.owner = :user')
			->andWhere('
            (cp.shiny = true AND cp.quantity > 0)
            OR
            (cp.shiny = false AND cp.quantity > 1 AND cp.pokemon NOT IN (
                SELECT p3.id
                FROM App\Entity\CapturedPokemon cp3
                JOIN cp3.pokemon p3
                WHERE cp3.owner = :connectedUser
                AND cp3.quantity > 0
            ))
        ')
			->setParameter('user', $user)
			->setParameter('connectedUser', $connectedUser)
			->getQuery()
			->getResult();
	}
}
