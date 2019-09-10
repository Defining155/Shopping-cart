<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use mysql_xdevapi\Session;
use PhpParser\Builder\Class_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }


    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/add-cart", name="product_add", methods={"GET","POST"})
     */
    public function add(Request $request, Product $product, ProductRepository $productRepository): Response
    {

        $cart = $this->session->get("Product", []);
        $id = $product->getId();

        if (isset($cart[$product->getId()])) {
            $cart[$product->getId()]["Amount"]++;
        } else {
            $cart[$product->getId()]["Amount"] = 1;
        }

        $this->session->set("Product", $cart);

        var_dump($this->session->get('Product'));
        return $this->render('product/add.html.twig', [
            'product' => $productRepository->findAll(),
        ]);
    }

    /**
 * @Route("/{id}/show-cart", name="product_show", methods={"GET","POST"})
 */
    public function show(product $details, productRepository $productRepository)
    {

        $cart = $this->session->get("Product", []);
        $newcart = array();
        foreach ($cart as $id => $details) {
            array_push($newcart, ["Amount" => $details["Amount"], "Product" => $productRepository->find($details->getId())]);
        }
        return $this->render('cart/index. html.twig', [
            "products" => $newcart,
        ]);
    }



    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index');
    }
}

