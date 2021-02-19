<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => "Nom de la recette"],
                'label' => 'Nom'
            ])
            ->add('category', EntityType::class, [ 
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie'
            ])
            ->add('Ingredients', TextareaType::class, [
                'attr' => ['placeholder' => "Liste des ingrédients"],
                'label' => 'Ingrédients'
            ])
            ->add('preparation', TextareaType::class, [
                'attr' => ['placeholder' => "Etapes de la préparation"],
                'label' => 'Préparation'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
