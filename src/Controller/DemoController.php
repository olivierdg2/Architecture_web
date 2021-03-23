<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Recette;
use App\Repository\RecetteRepository;
use App\Repository\CategoryRepository;
use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\RecetteType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Entity\Ingredients;
use Psr\Log\LoggerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use App\Service\FileUploader;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DemoController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(RecetteRepository $repo, Request $request, NormalizerInterface $normalizer): Response
    {
        //Find all recipes in the RecetteRepository
        $recettes = $repo->findAll();

        //Search filter
        $filter = "";
        if($request->query->has('filter')){
            $filter = $request->get('filter');
        }
        
        return $this->render('demo/index.html.twig', [
            'recettes' => $recettes,
            'filter' => $filter
        ]);
    }

    /**
     * @Route("/categories", name="categories")
     */
    public function categories(CategoryRepository $repo, Request $request): Response
    {
        //Find all category in the CategoryRepository
        $categories = $repo->findAll();

        //Search filter
        $filter = "";
        if($request->query->has('filter')){
            $filter = $request->get('filter');
        }

        return $this->render('demo/categories.html.twig', [
            'categories' => $categories,
            'filter' => $filter
        ]);
    }

    /**
     * @Route("/recette/new_recette", name="create_r")
     * @Route("/recette/{id}/edit", name="edit_r")
     */
    public function create_recette(Recette $recette = null, Request $request, EntityManagerInterface $manager, FileUploader $fileUploader) {

        //If it's edit mode (no recette sent) create a new Recette with empty fields 
        if(!$recette){
            $recette = new Recette();
        }
        //Must reset category 
        else{
            $recette->setCategory(null);
        }
        if ($recette->getImage()){
            $recette->setImage(
                new File($this->getParameter('images_directory').'/'.$recette->getImage())
            );
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
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $recette->setImage($imageFileName);
            }
            $recette->setIngredients($form->get('Ingredients')->getData());
            $recette->setPreparation($form->get('Preparation')->getData());
            //Persist and flush the new recipe to the db 
            $manager->persist($recette);
            $manager->flush();
            //redirect to the finished recipe page 
            return $this->redirectToRoute('show_r', ['id' => $recette->getId()]);
        }

        return $this->render('demo/create_recette.html.twig',[
            'formRecette' => $form->createView(),
            'editMode' => $recette->getId() != null
        ]);
    }

    /**
     * @Route("/category/new_category", name="create_c")
     * @Route("/category/{id}/edit", name="edit_c")
     */
    public function create_category(Category $category = null, Request $request, EntityManagerInterface $manager) {

        //If it's edit mode (no category sent) create a new Categpry with empty fields 
        if(!$category){
            $category = new Category();
        }

        //Create form according to the category's format 
        $form = $this->createForm(CategoryType::class, $category);
        
        //Wait for request from the form 
        $form->handleRequest($request);

        //When the form is validated
        if($form->isSubmitted() && $form->createView()) {
            //Persist and flush the new category to the db 
            $manager->persist($category);
            $manager->flush();
            //redirect to the finished category page 
            return $this->redirectToRoute('show_c', ['id' => $category->getId()]);
        }

        return $this->render('demo/create_category.html.twig',[
            'formCategory' => $form->createView(),
            'editMode' => $category->getId() != null
        ]);
    }

    /**
     * @Route("/recette/{id}", name="show_r")
     */
    public function recette_show(Recette $recette): Response
    {
        return $this-> render("demo/recette_show.html.twig", [
            'recette' => $recette
        ]);
    }

    /**
     * @Route("/category/{id}", name="show_c")
     */
    public function category_show(Category $category): Response
    {
        return $this-> render("demo/category_show.html.twig", [
            'category' => $category
        ]);
    }

    /**
     * @Route("/recette/{id}/delete", name="delete_r")
     */
    public function delete_recette(Recette $recette, EntityManagerInterface $manager, RecetteRepository $repo): Response
    {
        $manager->remove($recette);
        $manager->flush();

        //Find all recipes in the RecetteRepository
        $recettes = $repo->findAll();
        return $this->redirectToRoute('home', [
            'recettes' => $recettes
        ]);
    }

    /**
     * @Route("/category/{id}/delete", name="delete_c")
     */
    public function delete_category(Category $category, EntityManagerInterface $manager, CategoryRepository $repo): Response
    {
        $manager->remove($category);
        $manager->flush();

        //Find all category in the CategoryRepository
        $categories = $repo->findAll();
        return $this->redirectToRoute('categories', [
            'categories' => $categories
        ]);
    }
}
