<?php

namespace App\Controller\User;

use App\Entity\Currency;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserDataController extends AbstractController
{

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/main/user", name="app_user.edit", methods="GET|POST")
     */
    public function edit(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordEncoder)
    {
        $user = $this->getUser();
        if ($request->isMethod('POST')) {
            if (!empty($request->request->get('user')) && !empty($request->request->get('email')) && !empty($request->request->get('currency'))) {
                $user->setName($request->request->get('user'));
                $user->setEmail($request->request->get('email'));
                $user->setCurrencyId($request->request->get('currency'));
                if (!empty($request->request->get('pass1')) or !empty($request->request->get('pass2'))) {
                    if ($request->request->get('pass1') == $request->request->get('pass2')) {
                        $user->setPassword($passwordEncoder->hashPassword($user, $request->request->get('pass1')));
                        $this->em->flush();
                        $this->addFlash('success', 'Password successfully saved');
                    } else {
                        $this->addFlash('error', 'Passwords are different');
                    }
                } else {
                    $this->em->flush();
                    $this->addFlash('success', 'Datas successfully saved');
                }
            } else {
                $this->addFlash('error', 'You must set a user name, an email and a currency');
            }
        }

        $all_currency = $doctrine->getRepository(Currency::class)->findAll();
        return $this->render('user/edit.html.twig', [
            'currency' => $all_currency,
        ]);
    }
}
