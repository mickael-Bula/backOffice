<?php

namespace App\Controller;

use App\Entity\{Category, Product};
use App\Form\CategoryFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/category")
 * 
 * @isGranted("ROLE_ADMIN")
 */
class CategoryController extends AbstractController
{
    private $categoriesRepository;

    public function __construct(ManagerRegistry $doctrine)
    {
        // on récupère le repository Category
        $this->categoriesRepository = $doctrine->getRepository(Category::class);
    }

    /**
     * @Route("/", name="app_categories")
     */
    public function categories(): Response
    {
        // on transmet à la vue les catégories
        $categories = $this->categoriesRepository->findAll();

        $title = "Liste des catégories";

        return $this->render('category/categories.html.twig', compact("categories", "title"));
    }

    /**
     * @Route("/category/{id}", name="app_category_show", requirements={"id"="\d+"})
     */

    public function show(ManagerRegistry $doctrine, Request $request, ?int $id): Response
    {
        // récupération du nom de la catégorie courante
        $category = $this->categoriesRepository->find($id);
        $title = "Produits de la catégorie {$category->getName()}";

        // récupération des produits de la catégorie courante
        $productRepository = $doctrine->getRepository(Product::class);
        $products = $productRepository->findBy(["category" => $id]);

        return $this->render('home/products.html.twig', compact("products", "title"));
    }

    /**
     * Méthode utilisée à la fois pour la création et la mise à jour d'une catégorie
     * 
     * @Route("/update/{id?}", name="app_update_category", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */

    public function update(ManagerRegistry $doctrine, Request $request, ?int $id): Response
    {
        // si l'id est présent, on récupère la catégorie...
        if ($id !== null) {
            $category = $this->categoriesRepository->find($id);
            $form_title = "Modifier une catégorie";
            // ...sinon on en crée une nouvelle
        } else {
            $category = new Category();
            $form_title = "Ajouter une catégorie";
        }

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            // si le formulaire a été soumis on redirige vers la liste des produits
            return $this->redirectToRoute("app_categories");
        }

        return $this->renderForm("category/category-form.html.twig", compact("form_title", "form"));
    }

    /**
     * Méthode pour supprimer une catégorie
     * 
     * @Route("/delete/{id}", name="app_delete_category", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */

    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $category = $this->categoriesRepository->find($id);
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute("app_categories");
    }
}
