<?php

namespace App\Controller;

use App\classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session/{reference}', name: 'app_stripe_create_session')]
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference): Response
    {
        $product_for_stripe = []; //stripe d'une variable de stripe a commenter si on veut enlever le paiement stripe
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';  //stripe d'une variable de stripe a commenter si on veut enlever le paiement stripe

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order) {
            new JsonResponse(['error' => 'order']);
        }

        //stripe d'une variable de stripe a commenter si on veut enlever le paiement stripe
        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_object = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
            $product_for_stripe[] = [
                'price_data' => [
                    'currency' => 'xof',
                    'unit_amount' => $product->getPrice() / 100,
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN."/uploads/".$product_object->getIllustration()],

                    ]
                ],
                'quantity' => $product->getQuantity(),
            ];
            //stripe d'une variable de stripe a commenter si on veut enlever le paiement stripe
        }

        $product_for_stripe[] = [
            'price_data' => [
                'currency' => 'xof',
                'unit_amount' => ($order->getCarrierPrice() / 100),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN."/uploads/".$product_object->getIllustration()],

                ]
            ],
            'quantity' => 1,
        ];

        Stripe::setApiKey('sk_test_51MHx6QIqAHiWRjoK3LW0CfJY3TuyNfShHetWCxGsyxNX7YWawsVfiSIZ7zYsyUjL0lWmQR6wyfyLKM2RlB9E5SpJ00LMCayUjp');

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            /*'line_items' => [[
                 # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
                 'price' => ['$product->getId()'],
                 'quantity' => 1,
             ]],*/  //fourni avec la documentation de stripe
            'line_items' => [[
                # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
                $product_for_stripe
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId ($checkout_session->id);
        $entityManager->flush();
        //$order->setStripeIdSession($checkout_session->id);

        $response = new JsonResponse(['id' => $checkout_session->id]);
        return $response;
    }
}
