<?php

namespace App\Repository;

use App\Entity\CapturedPokemon;
use App\Entity\Pokemon;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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



    public function findShinyCaptured(User $user): array
    {
        return $this->createQueryBuilder('cp')
            ->select('DISTINCT p.pokeId')
            ->innerJoin('cp.pokemon', 'p')
            ->where('cp.owner = :userId')
            ->andWhere('cp.shiny = true')
            ->setParameter('userId', $user->getId())
            ->orderBy('p.pokeId', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }



}
