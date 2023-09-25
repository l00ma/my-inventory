<?php

namespace App\Controller\User;

use App\Form\UserType;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserDataController extends AbstractController
{

    #[Route('/main/user', name: 'app_user.edit', methods: ['GET|POST'])]
    public function edit(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordEncoder)
    {
        $user = $this->getUser();
        $currentPassword = $user->getPassword();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!empty($form["password"]->getData())) {
                $user->setPassword($passwordEncoder->hashPassword($user, $form["password"]->getData()));
            }
            else {
                $user->setPassword($currentPassword);
            }
            try {
                $userRepository->save($user, true);
                $this->addFlash('success', 'User datas successfully saved');
            } catch (Exception $ex) {
                $this->addFlash('error', 'You must set a user name, an email and a currency');
            }

            return $this->redirectToRoute('app_product_index');
        }

        return $this->renderForm('user/edit.html.twig', [
            'form' => $form
        ]);
    }
}
