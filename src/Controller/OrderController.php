<?php

namespace App\Controller;

use App\classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }



    #[Route('/commande', name: 'app_order')]
    public function index(Request $request ,Cart $cart): Response
    {
        if (!$this->getUser()->getAddresses()->getValues()) {
            return $this->redirectToRoute('app_account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getfull()
        ]);
    }
    #[Route('/commande/recapitulatif', name: 'app_order-recap', methods:['POST'])]
    public function add(Request $request ,Cart $cart): Response
    {
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = new \DateTimeImmutable();
            $carriers = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();
            $delivery_content = $delivery->getFirstname().''.$delivery->getLastname();
            $delivery_content .= '<br/>'.$delivery->getPhone();

            if ($delivery->getCompagny()) {
                $delivery_content .= '<br/>'.$delivery->getCompagny();
            }

            $delivery_content .= '<br/>'.$delivery->getAddress();
            $delivery_content .= '<br/>'.$delivery->getPostal().''.$delivery->getCity();
            $delivery_content .= '<br/>'.$delivery->getCountry();

            //enregister commande order;
            $order = new Order();
            $reference = $date->format('dmY').'-'.uniqid();
            $order->setReference($reference);
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setCarrierName($carriers->getName());
            $order->setCarrierPrice($carriers->getPrice());
            $order->setDelivery($delivery_content);
            $order->setState(0);

            $this->entityManager->persist($order);

            // $product_for_stripe = []; //stripe d'une variable de stripe a commenter si on veut enlever le paiement stripe
            // $YOUR_DOMAIN = 'http://127.0.0.1:8000';  //stripe d'une variable de stripe a commenter si on veut enlever le paiement stripe

            //enregister commande orderdetails;
            foreach ($cart->getfull() as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyorder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);

                $this->entityManager->persist($orderDetails);


            }

            $this->entityManager->flush();


            return $this->render('order/add.html.twig', [
                'cart' => $cart->getfull(),
                'carrier' => $carriers,
                'delivery' => $delivery_content,
                'reference' => $order->getReference()
            ]);

        }
        return $this->redirectToRoute('app_cart');

    }
}
