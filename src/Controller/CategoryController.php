<?php

namespace App\Controller;

use App\Form\CategoryFormType;
use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/category")
 *
 * @IsGranted("ROLE_ADMIN")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="app_categories")
     */
    public function categories(CategoryRepository $categoryRepository): Response
    {
        // on transmet à la vue les catégories
        $categories = $categoryRepository->findAll();

        $title = "Liste des catégories";

        return $this->render('category/categories.html.twig', compact("categories", "title"));
    }

    /**
     * @Route("/category/{id}", name="app_category_show", requirements={"id"="\d+"})
     */

    public function show(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ?int $id
        ): Response
    {
        // récupération du nom de la catégorie courante
        $category = $categoryRepository->find($id);
        $title = "Produits de la catégorie {$category->getName()}";

        // récupération des produits de la catégorie courante
        $products = $productRepository->findBy(["category" => $id]);

        return $this->render('home/products.html.twig', compact("products", "title"));
    }

    /**
     * Méthode utilisée à la fois pour la création et la mise à jour d'une catégorie
     * 
     * @Route("/update/{id?}", name="app_update_category", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */

    public function update(
        EntityManagerInterface $entityManager,
        Request $request,
        ?Category $id   // en injectant Category - lié à id - , le paramConverter fait $category = CategoryRepository->find($id)
        ): Response
    {
        // si la catégorie n'est pas précisée, on en crée une nouvelle...
        if (!$id) {
            $category = new Category();
            $form_title = "Créer une catégorie";
        } else {
            $category = $id;    // je renomme $id en $category pour plus de clarté ($id est une instance de Category)
            $form_title = "Modifier une catégorie";
        }

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            // ajout d'un flashMessage signalant l'enregistrement
            $this->addFlash('success', 'Modification sauvegardée !');

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

    public function delete(CategoryRepository $categoryRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $category = $categoryRepository->find($id);
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute("app_categories");
    }
}
