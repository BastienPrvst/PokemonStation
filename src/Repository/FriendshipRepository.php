<?php

namespace App\Repository;

use App\Entity\Friendship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friendship>
 *
 * @method Friendship|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friendship|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friendship[]    findAll()
 * @method Friendship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    public function save(Friendship $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Friendship $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
