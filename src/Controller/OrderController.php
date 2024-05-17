<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{

    private $orderRepository;
    private $entityManager;

    public function __construct(OrderRepository $orderRepository,ManagerRegistry $doctrine){
        $this->orderRepository = $orderRepository;
        $this->entityManager = $doctrine->getManager();
    }

    
    #[Route('/orders', name: 'orders_list')]
    public function index(): Response
    {
        $orders = $this->orderRepository->findAll();
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/user/orders', name: 'user_order_list')]
    public function userOrders(): Response
    {
        if(!$this->getUser()){

            return $this->redirectToRoute('app_login');
        }
        return $this->render('order/user.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
    
    #[Route('/store/order/{productId}', name: 'order_store')]
    public function store($productId): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
    
        // Récupérer le produit en fonction de son ID
        $product = $this->entityManager->getRepository(Product::class)->find($productId);
    
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
    
        $orderExists = $this->orderRepository->findOneBy([
            'user' => $this->getUser(),
            'pname' => $product->getName()
        ]);

        if($orderExists){
             $this->addFlash('Warning', 'Your have already ordered this product !');
        return $this->redirectToRoute('user_order_list');
        }

        // Créer une nouvelle commande avec les détails du produit
        $order = new Order();
        $order->setPname($product->getName());
        $order->setPrice($product->getPrice());
        $order->setStatus('processing....');
        $order->setUser($this->getUser());
    
        // Enregistrer la commande dans la base de données
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    
        // Rediriger l'utilisateur vers sa liste de commandes
        $this->addFlash('success', 'Your order was saved !');
        return $this->redirectToRoute('user_order_list');
    }
    
    #[Route('/update/order/{orderId}/{status}', name: 'order_status_update')]
    public function updateOrderStatus($orderId, $status): Response
    {
        // Récupérer l'objet Order à partir de l'ID fourni dans l'URL
        $order = $this->entityManager->getRepository(Order::class)->find($orderId);
    
        // Vérifier si l'objet Order existe
        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }
    
        // Mettre à jour le statut de la commande
        $order->setStatus($status);
    
        // Enregistrer les modifications dans la base de données
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    
        // Rediriger l'utilisateur vers la liste des commandes
        $this->addFlash('success', 'Your order status was updated !');
        return $this->redirectToRoute('orders_list');
    }
    
    #[Route('/update/order/{orderId}', name: 'order_delete')]
    public function deleteOrder($orderId): Response
    {
        // Récupérer l'objet Order à partir de l'ID fourni dans l'URL
        $order = $this->entityManager->getRepository(Order::class)->find($orderId);
            
        // Vérifier si l'objet Order existe
        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        $this->entityManager->remove($order);
        $this->entityManager->flush();
    
        // Rediriger l'utilisateur vers la liste des commandes
        $this->addFlash('success', 'Your order was deleted !');
        return $this->redirectToRoute('orders_list');
    }

    #[Route('/update/order_user/{orderId}', name: 'order_delete_user')]
    public function deleteOrderuser($orderId): Response
    {
        // Récupérer l'objet Order à partir de l'ID fourni dans l'URL
        $order = $this->entityManager->getRepository(Order::class)->find($orderId);
            
        // Vérifier si l'objet Order existe
        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        $this->entityManager->remove($order);
        $this->entityManager->flush();
    
        // Rediriger l'utilisateur vers la liste des commandes
        $this->addFlash('success', 'Your order was deleted !');
        return $this->redirectToRoute('user_order_list');
    }

}
