<?php

namespace App\Controller;

use App\classe\Cart;
use App\classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/merci/{stripeSessionId}', name: 'app_order_validate')]
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($order->getState() == 0) {
            //vider la session cart
            $cart->remove();

            //modifier le statut de notre commande a 1
            $order->setState(1);
            $this->entityManager->flush();

            //envoyer une email de confirmation
            $mail = new Mail();
            $content = "Bonjour ".$order->getUser()->getFirstname()."<br>Merci de nous faire confiance";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande est bien validée', $content);
        }


        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
