<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{   

    private $productRepository;
    private $entityManager;

    public function __construct(ProductRepository $productRepository,ManagerRegistry $doctrine){
        $this->productRepository = $productRepository;
        $this->entityManager = $doctrine->getManager();
    }
    
    #[Route('/product', name: 'product_list')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/store/product', name: 'product_store')]
    public function store(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class,$product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $product = $form->getData();
           if($request->files->get('product')['image']){
                $image = $request->files->get('product')['image'];
                $image_name = time().'_'.$image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'),$image_name);
                $product->setImage($image_name);
           }
           $this->entityManager->persist($product);
           $this->entityManager->flush();
           $this->addFlash('success','Your product was saved !');
           
           return $this->redirectToRoute('product_list');
        }

        return $this->renderForm('product/create.html.twig', [
            'form' => $form
        ]);
    }

   #[Route('/product/details/{id}', name: 'product_show')]
public function show($id): Response
{
    $product = $this->entityManager->getRepository(Product::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Product not found');
    }

    return $this->render('product/show.html.twig', [
        'product' => $product,
        'photo_url'=> 'http://127.0.0.1:8000/uploads/'
    ]);
}


    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function editProduct($id, Request $request): Response
    {
        $product = $this->productRepository->find($id); // Charger l'entité Product correspondante
    
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
    
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($request->files->get('product')['image']) {
                $image = $request->files->get('product')['image'];
                $image_name = time() . '_' . $image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'), $image_name);
                $product->setImage($image_name);
            }
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash('success', 'Your product was updated !');
    
            return $this->redirectToRoute('product_list');
        }
    
        return $this->renderForm('product/edit.html.twig', [
            'form' => $form,
            'product' => $product // Vous pouvez passer l'entité Product au template si vous en avez besoin
        ]);
    }
    
    
    #[Route('/product/delete/{id}', name: 'product_delete')]
    public function delete(int $id): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        $filesystem = new Filesystem();  
        $imagepath = './uploads/'.$product->getImage();
        if($filesystem->exists($imagepath)){
            $filesystem->remove($imagepath);
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
        $this->addFlash('success','Your product was removed !');

        return $this->redirectToRoute('product_list');
    }
}
