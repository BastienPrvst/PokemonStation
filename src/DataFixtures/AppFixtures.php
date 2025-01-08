<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        // Création des pokémons à partir du fichier "Documents/SQL/pokemons.sql"
        $finder = new Finder();
        $finder->in('Documents/SQL');
        $finder->name('pokemons.sql');

        foreach( $finder as $file ){
            $content = $file->getContents();

            $stmt = $manager->getConnection()->prepare($content);
            $stmt->execute();
        }

        $manager->flush();
        
    }
}
