<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    /**
     * @var UserRepository;
     */
    private $repository;

    public function __construct(UserRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    #[Route('/main', name: 'app_main')]
    public function index(): Response
    {
        $role = $this->getUser()->getRoles();

        foreach ($role as $admin) {
            if ($admin == "ROLE_ADMIN") {
                return $this->redirectToRoute('app_admin');
            } else {
                return $this->render('main/index.html.twig', [
                    'controller_name' => 'MainController',
                ]);
            }
        }
    }
}
