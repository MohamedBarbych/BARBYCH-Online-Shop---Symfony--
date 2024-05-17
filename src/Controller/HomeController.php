<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $productRepository;
    private $categoryRepository;
    private $entityManager;

    public function __construct(ProductRepository $productRepository,CategoryRepository $categoryRepository ,ManagerRegistry $doctrine){
       
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        $categories = $this->categoryRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'categories' => $categories,

        ]);
    }

    #[Route('/product/{categoryId}', name: 'product_category')]
    public function categoryProducts(int $categoryId): Response
    {
        $category = $this->categoryRepository->find($categoryId);
    
        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'a pas été trouvée');
        }
    
        $categories = $this->categoryRepository->findAll();
    
        return $this->render('home/index.html.twig', [
            'products' => $category->getProducts(),
            'categories'=> $categories,
        ]);
    }
    
}
