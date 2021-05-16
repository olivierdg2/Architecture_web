<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\IngredientType;
use App\Form\StepType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => "Nom de la recette"],
                'label' => False
            ])
            ->add('category', EntityType::class, [ 
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => False
            ])
            ->add('Ingredients', CollectionType::class, [
                'entry_type' => IngredientType::class,
                'allow_add' => True,
                'allow_delete' => True,
                'delete_empty' => True,
                'label' => False
            ])
            ->add('Preparation', CollectionType::class, [
                'entry_type' => StepType::class,
                'allow_add' => True,
                'allow_delete' => True,
                'delete_empty' => True,
                'label' => False
            ])
            ->add('image', FileType::class, [
                'label' => False,
                'required' => False
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
