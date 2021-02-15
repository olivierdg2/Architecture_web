<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Recette;
use App\Entity\Category;

class RecetteFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $plat = new Category();
        $plat->setName("Plats")
            ->setDescription("chauds");
        $des = new Category();
        $des->setName("Desserts")
            ->setDescription("sucré");
        $manager->persist($plat);
        $manager->persist($des);
        for($i = 1; $i <= 10; $i++){
            $recette = new Recette();
            $recette->setName("Nom n°$i")
            ->setCreatedAt(new \datetime())
            ->setIngredients("<p>Sucre</p><p>Farine</p>")
            ->setPreparation("blabla");
            if($i%2 == 0){
                $recette->setCategory($plat);
            }
            else{
                $recette->setCategory($des);
            }
            $manager->persist($recette);
        }

        // $product = new Product();
        // $manager->persist($product);
    
        $manager->flush();
    }
}
