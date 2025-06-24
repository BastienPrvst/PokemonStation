<?php

namespace App\Factory;

use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'creationDate' => self::faker()->dateTime(),
            'email' => self::faker()->email(),
            'lastObtainedLaunch' => self::faker()->dateTime(),
            'launchs' => self::faker()->randomNumber(),
            'password' => self::faker()->password,
            'pseudonym' => self::faker()->text(20),
            'roles' => [],
            'launch_count' => self::faker()->randomNumber(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(User $user): void {})
        ;
    }
}
