<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\Peremption;
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
    public function index(Peremption $peremption): Response
    {
        $role = $this->getUser()->getRoles();

        foreach ($role as $admin) {
            if ($admin == "ROLE_ADMIN") {
                return $this->redirectToRoute('app_admin');
            } else {
                // on recupère les dates de peremption en assignant des valeurs en fonction de la date de peremption
                $peremption->getPeremption($this->getUser());
                return $this->render('main/index.html.twig');
            }
        }
    }
}
