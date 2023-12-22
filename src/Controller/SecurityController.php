<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/reset', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mail
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //On va chercher l'utilisateur par son email
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // On vérifie si on a un utilisateur
            if ($user) {
                // On génère un token de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // On génère un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // On crée les données du mail
                $context = compact('url', 'user');

                // Envoi du mail
                $mail->send(
                    'contact@my-inventory.fred-moutin.com',
                    $user->getEmail(),
                    'Password reset',
                    'password_reset',
                    $context
                );

                $this->addFlash('success', 'Email successfully sent.');
                return $this->redirectToRoute('app_login');
            }
            // $user est null
            $this->addFlash('error', 'A problem occurs.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    #[Route('/reset/{token}', name: 'reset_pass')]
    public function resetPass(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // On vérifie si on a ce token dans la base
        $user = $userRepository->findOneByResetToken($token);

        // On vérifie si l'utilisateur existe

        if ($user) {
            $form = $this->createForm(ResetPasswordFormType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // On efface le token
                $user->setResetToken('');

                // On enregistre le nouveau mot de passe en le hashant
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Password successfully changed.');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }

        // Si le token est invalide on redirige vers le login
        $this->addFlash('error', 'Invalid token.');
        return $this->redirectToRoute('app_login');
    }
}
