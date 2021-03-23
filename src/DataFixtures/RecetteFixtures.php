<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Recette;
use App\Entity\Category;
use App\Entity\Ingredient;
use App\Entity\Step;

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
            $ing = new Ingredient();
            $ing->setIngredient("farine");
            $ing->setQuantity("4");
            $ing2 = new Ingredient();
            $ing2->setIngredient("sucre");
            $ing2->setQuantity("2");
            $recette->setName("Nom n°$i")
            ->setCreatedAt(new \datetime());
            $recette->addIngredient($ing);
            $recette->addIngredient($ing2);
            $step1 = new Step();
            $step1->setStep("a");
            $step2 = new Step();
            $step2->setStep("b");
            $recette->addPreparation($step1);
            $recette->addPreparation($step2);
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
