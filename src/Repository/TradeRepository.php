<?php

namespace App\Repository;

use App\Entity\Trade;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trade>
 */
class TradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trade::class);
    }

	/**
	 * @throws NonUniqueResultException
	 */
	public function tradeExist(User $user1, User $user2): Trade|null
	{
		return $this->createQueryBuilder('t')
			->where('(t.user1 = :user1 AND t.user2 = :user2) OR (t.user1 = :user2 AND t.user2 = :user1)')
			->andWhere('t.status != 3 AND t.status != 4')
			->setParameter('user1', $user1)
			->setParameter('user2', $user2)
			->getQuery()
			->getOneOrNullResult();
	}
}
