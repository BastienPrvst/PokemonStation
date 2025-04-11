<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<News>
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function findRecent()
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.creationDate', 'DESC')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();
    }
}
