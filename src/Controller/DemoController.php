<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Recette;
use App\Repository\RecetteRepository;
use App\Repository\CategoryRepository;
use App\Entity\Category;
use App\Entity\Ingredient;
use App\Form\CategoryType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\RecetteType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Entity\Ingredients;
use App\Entity\Preparation;
use App\Entity\Step;
use Psr\Log\LoggerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Date;

class DemoController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(RecetteRepository $repo, Request $request): Response
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

    /**
     * @Route("/api/recettes", name="api_get_recettes", methods={"GET"})
     */
    public function api_get_recettes(RecetteRepository $repo, Request $request, NormalizerInterface $normalizer)
    {
        //Find all recette in the RecetteRepository then return it in the Json format
        return $this->json($repo->findAll(), 200, [], ['groups' => "recette:read"]);
    }

    /**
     * @Route("/api/recette/{id}", name="api_get_recette_by_id", methods={"GET"},requirements={"id":"\d+"})
     */
    public function api_get_recette_by_id($id,RecetteRepository $repo, Request $request, NormalizerInterface $normalizer)
    {
        $recette = $repo->find($id);
        if ($recette != null){
            //Find a recette from its id in the RecetteRepository then return it in the Json format
            return $this->json($repo->find($id), 200, [], ['groups' => "recette:read"]);
        }
        else {
            return $this->json([
                'status' => 400,
                'message' => "No recette related to the given id"
            ], 400);   
        }
    }

    /**
     * @Route("/api/recettes", name="api_set_recettes", methods={"POST"})
     */
    public function api__post_recette(RecetteRepository $repo,CategoryRepository $cat_repo, Request $request, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        //Convert Json request to Array
        $json_rec = $request->toArray();
        //Check if all information needed are given 
        if (isset($json_rec["Name"])  && isset($json_rec["Category"]) && isset($json_rec["Ingredients"]) && isset($json_rec["Preparation"])){
            //If so, create a new Recette and set its properties with given elements
            $recette = new Recette();
            $recette->setName($json_rec["Name"]);
            //Special case of the image, if it's not given, set it to null 
            if (isset($json_rec["Image"])){
                $recette->setImage($json_rec["Image"]);
            }
            else{
                $recette->setImage(null);
            }
            
            //Only Category's id is given, we must retrieve the entity 
            $cat=$cat_repo->find($json_rec["Category"]);
            $recette->setCategory($cat);
            //Ingredients is made of Ingredient objects
            $json_ing = $json_rec["Ingredients"];
            $ingredients = [];
            foreach ($json_ing as $element){
                $ing = new Ingredient();
                $ing->setIngredient($element["Ingredient"]);
                $ing->setQuantity($element["Quantity"]);
                array_push($ingredients,$ing);
            }
            $recette->setIngredients($ingredients);
            //Preparation is made of Step objects
            $json_prep = $json_rec["Preparation"];
            $preparation = [];
            foreach ($json_prep as $element){
                $step = new Step();
                $step->setStep($element);
                array_push($preparation,$step);
            }
            $recette->setPreparation($preparation);
            $recette->setCreatedAt(new \DateTime());
            
            $em->persist($recette);
            $em->flush();

            //Return the created recette 
            return $this->json($recette, 201, [], ['groups' => 'recette:read']);
        }
        else {
            return $this->json([
                'status' => 400,
                'message' => "One or more of the following attributes were not given : Name, Category, Ingredients, Preparation"
                
            ], 400);
        }
    }

    /**
     * @Route("/api/recette/{id}", name="api_update_recette", methods={"PUT"},requirements={"id":"\d+"})
     */
    public function api__put_recette($id,RecetteRepository $repo,CategoryRepository $cat_repo, Request $request, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        //Convert Json request to Array
        $json_rec = $request->toArray();
        //Retrieve recette from its id
        $recette = $repo->find($id);
        //Check if there is a match 
        if ($recette != null){
            //If so, check if all needed information are given 
            if (isset($json_rec["Name"])  && isset($json_rec["Category"]) && isset($json_rec["Ingredients"]) && isset($json_rec["Preparation"])){
                $recette->setName($json_rec["Name"]);

                if (isset($json_rec["Image"])){
                    $recette->setImage($json_rec["Image"]);
                }
                
                $cat=$cat_repo->find($json_rec["Category"]);
                $recette->setCategory($cat);

                $json_ing = $json_rec["Ingredients"];
                $ingredients = [];
                foreach ($json_ing as $element){
                    $ing = new Ingredient();
                    $ing->setIngredient($element["Ingredient"]);
                    $ing->setQuantity($element["Quantity"]);
                    array_push($ingredients,$ing);
                }
                $recette->setIngredients($ingredients);
                $json_prep = $json_rec["Preparation"];
                $preparation = [];
                foreach ($json_prep as $element){
                    $step = new Step();
                    $step->setStep($element);
                    array_push($preparation,$step);
                }
                $recette->setPreparation($preparation);
                
                $em->persist($recette);
                $em->flush();
                
                //Return the modified recette 
                return $this->json($recette, 201, [], ['groups' => 'recette:read']);
            }
            else {
                return $this->json([
                    'status' => 400,
                    'message' => "One or more of the following attributes were not given : Name, Category, Ingredients, Preparation"
                    
                ], 400);
            }
        }
        else {
            return $this->json([
                'status' => 400,
                'message' => "No recette related to the given id"
            ], 400);
        }   
    }
    /**
     * @Route("/api/recettes/{id}", name="api_delete_recettes", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function api_delete_recettes($id, RecetteRepository $repo, Request $request, NormalizerInterface $normalizer, EntityManagerInterface $em)
    {
        $recette = $repo->find($id);
        if ($recette != null){
            $em->remove($recette);
            $em->flush();
            return $this->json(
                [
                'status' => 200,
                'message' => "Deleted recette successfuly"
            
            ]);
        }
        else {
            return $this->json([
                'status' => 400,
                'message' => "No recette related to the given id"
            ], 400);
        }
    }

    /**
     * @Route("/api/categories", name="api_get_categories", methods={"GET"})
     */
    public function api_get_categories(CategoryRepository $repo, Request $request, NormalizerInterface $normalizer)
    {
        //Find all actegory in the CategoryRepository then return it in the Json format
        return $this->json($repo->findAll(), 200, [], ['groups' => "category:read"]);
    }

    /**
     * @Route("/api/category/{id}", name="api_get_category_by_id", methods={"GET"},requirements={"id":"\d+"})
     */
    public function api_get_category_by_id($id, CategoryRepository $repo, Request $request, NormalizerInterface $normalizer)
    {
        $category = $repo->find($id);
        if ($category != null){
            //Find a category from its id in the CategoryRepository then return it in the Json format
            return $this->json($repo->find($id), 200, [], ['groups' => "category:read"]);
        }
        else {
            return $this->json([
                'status' => 400,
                'message' => "No category related to the given id"
            ], 400);   
        }
    }
    
    /**
     * @Route("/api/categories", name="api_set_category", methods={"POST"})
     */
    public function api__post_category(CategoryRepository $repo, Request $request, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        //Convert Json request to Array
        $json_rec = $request->toArray();
        //Check if all information needed are given 
        if (isset($json_rec["Name"])  && isset($json_rec["Description"])){
            //If so, create a new Recette and set its properties with given elements
            $category = new Category();
            $category->setName($json_rec["Name"]);
            $category->setDescription($json_rec["Description"]);
            $em->persist($category);
            $em->flush();

            //Return the created recette 
            return $this->json($category, 201, [], ['groups' => 'category:read']);
        }
        else {
            return $this->json([
                'status' => 400,
                'message' => "One or more of the following attributes were not given : Name, Description"
                
            ], 400);
        }
    }
    /**
     * @Route("/api/category/{id}", name="api_update_category", methods={"PUT"},requirements={"id":"\d+"})
     */
    public function api__put_category($id,CategoryRepository $repo, Request $request, EntityManagerInterface $em)
    {
        //Retrieve category from its id
        $category = $repo->find($id);
        //Check if there is a match 
        if ($category != null){
            //If so, check if all needed information are given 
            if (isset($json_rec["Name"])  && isset($json_rec["Description"]) ){
                $category->setName($json_rec["Name"]);
                $category->setDescription($json_rec["Description"]);
                
                $em->persist($category);
                $em->flush();
                
                //Return the modified category
                return $this->json($category, 201, [], ['groups' => 'category:read']);
            }
            else {
                return $this->json([
                    'status' => 400,
                    'message' => "One or more of the following attributes were not given : Name, Description"
                    
                ], 400);
            }
        }
        else {
            return $this->json([
                'status' => 400,
                'message' => "No category related to the given id"
            ], 400);
        }   
    }
    /**
     * @Route("/api/category/{id}", name="api_delete_category", methods={"DELETE"},requirements={"id":"\d+"})
     */
    public function api_delete_category($id, CategoryRepository $repo, Request $request, EntityManagerInterface $em)
    {
        $category = $repo->find($id);
        if ($category != null){
            $em->remove($category);
            $em->flush();
            return $this->json([
                'status' => 200,
                'message' => "Deleted category successfuly"
                
            ]);
        }
        else {
            return $this->json([
                'status' => 400,
                'message' => "No category related to the given id"
            ], 400);
        }
    }
}
