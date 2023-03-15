<?php

namespace App\Controller;

use App\classe\Mail;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/mot-de-passe-oublier', name: 'app_reset_password')]
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));

        if ($user) {
            // 1 : enregistrer en base la demande de reset
            $reset_password = new ResetPassword();
            $reset_password->setUser($user);
            $reset_password->setToken(uniqid());
            $reset_password->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($reset_password);
            $this->entityManager->flush();

            // 2 : envoyer un email à l'utilisateur
            $url = $this->generateUrl('app_update_password', [
                'token' => $reset_password->getToken()
            ]);
            $content = "Bonjour ".$user->getFirstname()."<br> Vous avez demander à réinitialiser votre mot de passe.<br><br>";
            $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe</a>.";

            $mail = new Mail();
            $mail->send($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), 'Renitialiser votre mot de passe', $content);


            $this->addFlash('notice', 'vous allez recevoir un mail avec la procedure de réinitialisation de votre mot de passe');

        }else {

            $this->addFlash('notice', 'cette adresse mail n\'est pas valide');
        }

        }

        return $this->render('reset_password/index.html.twig');
    }

    #[Route('/modifier-mot-de-passe/{token}', name: 'app_update_password')]
    public function update(Request $request, $token, UserPasswordHasherInterface $passwordHasher): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$reset_password) {
            return $this->redirectToRoute('app_reset_password');
        }

        //verifier si le createdAt = now - 3h
        $now = new \DateTimeImmutable();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            $this->addFlash('notice', 'votre demande de mot passe a expirer. Merci de la renouveller');
            return $this->redirectToRoute('app_reset_password');
        }

        //rendre une vue avec mot de passe
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new_pwd = $form->get('new_password')->getData();
            // encodage des mots de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $reset_password->getUser(), $new_pwd);

            $reset_password->getUser()->setPassword($hashedPassword);

            // flush en base de données
            $this->entityManager->flush();
            //redirection de l'utilisateur vers la page de connexion
            $this->addFlash('notice', 'votre mot de passe a bien été mis à jour');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);

    }
}
