<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\PeremptionService;
use DateTime;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
    public function index(PeremptionService $peremption, ManagerRegistry $doctrine): Response
    {
        $role = $this->getUser()->getRoles();

        foreach ($role as $admin) {
            if ($admin == "ROLE_ADMIN") {
                return $this->redirectToRoute('app_admin');
            } else {
                // on recupère les dates de peremption en assignant des valeurs en fonction de la date de peremption
                $peremption->getPeremption($this->getUser());

                $product_rep = $doctrine->getRepository(Product::class);
                $days = $this->getUser()->getPeremptionWarning();
                $soon = new DateTime();
                $soon->modify('+ ' . $days . ' days');

                return $this->render('main/index.html.twig', [
                    'perime' => $product_rep->findProductByDate(new DateTime(), new DateTime('1970-01-01'), $this->getUser()),
                    'soon_perime' => $product_rep->findProductByDate($soon, new DateTime(), $this->getUser()),
                ]);
            }
        }
    }
}
