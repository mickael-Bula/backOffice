<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\{ Request, Response };
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

        return $this->render('category/categories.html.twig', compact("categories"));
    }

    /**
     * @Route("/update/{id?}", name="app_update_category", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */

    public function update(ManagerRegistry $doctrine, Request $request, ?int $id): Response
    {
        if ( $id !== null) {
            $category = $this->categoriesRepository->find($id);
            $form_title = "Modifier une catégorie";
        } else {
            $category = new Category();
            $form_title = "Ajouter une catégorie";
        }

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            // si le formulaire a été soumis on redirige vers la liste des produits
            return $this->redirectToRoute("app_categories");
        }

        return $this->renderForm("category/category-form.html.twig", compact("form_title", "form"));
    }

    /**
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
