<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class HomeController extends AbstractController
{
    private $productsRepository;

    public function __construct(ManagerRegistry $doctrine)
    {
        // on récupère le repository Product
        $this->productsRepository = $doctrine->getRepository(Product::class);
    }

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
     public function products(): Response
     {
         // on transmet à la vue la totalité des produits existants en BDD
         $products = $this->productsRepository->findAll();

         return $this->render("home/products.html.twig", compact("products"));
     }

     /**
      * @IsGranted("ROLE_ADMIN")
      *
      * @Route("/product/{id}", name="app_product_id", methods={"GET"}, requirements={"id"="\d+"})
      */

     public function product(int $id): Response
     {
         $product = $this->productsRepository->find($id);

         return $this->render("home/product.html.twig", compact("product"));
     }

     /**
      * @IsGranted ("ROLE_ADMIN")
      *
      * @Route("/addProduct", name="app_add_product")
      */

     public function addProduct(ManagerRegistry $doctrine, Request $request): Response
     {
         $product = new Product();
         $form = $this->createForm(ProductFormType::class, $product);
         $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid())
         {
             $entityManager = $doctrine->getManager();
             $entityManager->persist($product);
             $entityManager->flush();
         }

         return $this->renderForm("product/product-form.html.twig", [
             "form_title" => "Ajouter un produit",
             "form" => $form
         ]);
     }
}
