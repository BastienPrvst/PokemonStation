<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $password = $this->userPasswordHasher->hashPassword(new User(), 'Admin123!');

        $user = (new User())
            ->setEmail('admin@email.com')
            ->setPassword($password)
            ->setPseudonym('admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setLaunchs(99999)
            ->setMoney(99999)
            ->setLastObtainedLaunch(new \DateTime())
            ->setCreationDate(new \DateTime());
        $manager->persist($user);
        $manager->flush();

        UserFactory::createMany(10);
    }
}
