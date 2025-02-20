<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $password = $this->userPasswordHasher->hashPassword(new User(), 'Admin123!');

        UserFactory::createOne([
            'email' => 'spirit@gmail.com',
            'password' => $password,
            'pseudonym' => 'Spirit',
            'roles' => ["ROLE_ADMIN"],
            'launchs' => 100,
            'creationDate' => new \DateTime(),
        ]);
        UserFactory::createMany(20);
        $manager->flush();
    }
}
