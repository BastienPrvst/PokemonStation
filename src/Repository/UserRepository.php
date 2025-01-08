<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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

        $this->save($user, true);
    }

    public function top10TotalSpeciesSeen(): array
    {
        ///SELECT user.*, COUNT(DISTINCT captured_pokemon.pokemon_id) as total_species_seen
        //FROM user
        //INNER JOIN captured_pokemon ON user.id = captured_pokemon.owner_id
        //INNER JOIN pokemon ON captured_pokemon.pokemon_id = pokemon.id
        //GROUP BY user.id
        //ORDER BY total_species_seen DESC
        //LIMIT 10;
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
//        SELECT user.*, COUNT(captured_pokemon.id) as total_pokemon_captured
//        FROM user
//        INNER JOIN captured_pokemon ON user.id = captured_pokemon.owner_id
//        GROUP BY user.id
//        ORDER BY total_pokemon_captured DESC
//        LIMIT 10;
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
