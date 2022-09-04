<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
// use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @Route("/products", name="app_products")
     */
     public function products(ProductRepository $productRepository): Response
     {
         // on transmet à la vue la totalité des produits existants en BDD
         $products = $productRepository->findAll();

         $title = "Liste des produits";

         return $this->render("home/products.html.twig", compact("products", "title"));
     }

    /**
     * Méthode pour mettre à jour ou créer un nouveau produit
     * 
     * @IsGranted ("ROLE_ADMIN")
     *
     * @Route("/product/update/{id?}", name="app_update_product", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */

    public function update(ProductRepository $productRepository, EntityManagerInterface $entityManager, Request $request, ?int $id): Response
    {
        // si l'id est présent, on récupère le produit...
        if ( $id !== null) {
            $product = $productRepository->find($id);
            $form_title = "Modifier un produit";
            // ...sinon on en crée un nouveau
        } else {
            $product = new Product();
            $form_title = "Ajouter un produit";
        }
        
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            // Le service EntityManagerInterface injecté est capable de trouver l'EntityManager de Product sans qu'il soit spécifié (sans doute à partir de l'objet passé en paramètre) 
            $entityManager->persist($product);
            $entityManager->flush();

            // si le formulaire a été soumis on redirige vers la liste des produits
            return $this->redirectToRoute("app_products");
        }

        return $this->renderForm("product/product-form.html.twig", compact("form_title", "form"));
    }

    /**
     * Méthode pour supprimer un produit
     * 
     * @IsGranted ("ROLE_ADMIN")
     *
     * @Route("/product/delete/{id}", name="app_delete_product", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */

    public function delete(ProductRepository $productRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $productRepository->find($id);
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute("app_products");
    }
}
