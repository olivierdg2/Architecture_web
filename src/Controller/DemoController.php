<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Recette;
use App\Repository\RecetteRepository;

class DemoController extends AbstractController
{
    /**
     * @Route("/demo", name="demo")
     */
    public function index(RecetteRepository $repo): Response
    {

        $recettes = $repo->findAll();
        return $this->render('demo/index.html.twig', [
            'controller_name' => 'DemoController',
            'recettes' => $recettes,
        ]);
    }
    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
            return $this->render('demo/home.html.twig', [
                    'title' => "Bienvenue",
                    'age' => 31
            ]);
    }

    /**
     * @Route("/demo/{id}", name="demo_show")
     */
    public function show(Recette $recette): Response
    {
        return $this-> render("demo/show.html.twig", [
            'recette' => $recette
        ]);
    }
}
