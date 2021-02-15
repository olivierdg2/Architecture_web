<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Recette;
use App\Repository\RecetteRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\RecetteType;

class DemoController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(RecetteRepository $repo): Response
    {
        //Find all recipes in the RecetteRepository
        $recettes = $repo->findAll();
        return $this->render('demo/index.html.twig', [
            'controller_name' => 'DemoController',
            'recettes' => $recettes,
        ]);
    }

    /**
     * @Route("/categories", name="categories")
     */
    public function categories(): Response
    {
        return $this->render('demo/categories.html.twig', [
            'title' => "Bienvenue",
            'age' => 31
        ]);
    }

    /**
     * @Route("/recette/new_recette", name="create_r")
     * @Route("/recette/{id}/edit", name="edit_r")
     */
    public function create_recette(Recette $recette = null, Request $request, EntityManagerInterface $manager,CategoryRepository $repo) {

        //If it's edit mode (no recette sent) create a new Recette with empty fields 
        if(!$recette){
            $recette = new Recette();
        }
        //Must reset category 
        else{
            $recette->setCategory(null);
        }

        //Create form according to the recette's format 
        $form = $this->createForm(RecetteType::class, $recette);
        
        //Wait for request from the form 
        $form->handleRequest($request);

        //When the form is validated
        if($form->isSubmitted() && $form->createView()) {
            //If it's edit mode set it a new date time 
            if(!$recette->getId()){
                $recette->setCreatedAt(new \DateTime());
            }
            //Persist and flush the new recip to the db 
            $manager->persist($recette);
            $manager->flush();
            //redirect to the finished recipe page 
            return $this->redirectToRoute('recette_show', ['id' => $recette->getId()]);
        }

        return $this->render('demo/create_recette.html.twig',[
            'formRecette' => $form->createView(),
            'editMode' => $recette->getId() != null
        ]);
    }

    /**
     * @Route("/recette/{id}", name="recette_show")
     */
    public function show(Recette $recette): Response
    {
        return $this-> render("demo/show.html.twig", [
            'recette' => $recette
        ]);
    }
}
