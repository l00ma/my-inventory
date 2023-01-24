<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\UserRepository;
use App\Service\PeremptionService;
use App\Service\ChartsService;
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
    public function index(PeremptionService $peremption, ChartsService $charts, ManagerRegistry $doctrine): Response
    {
        $role = $this->getUser()->getRoles();
        $user = $this->getUser();

        foreach ($role as $admin) {
            if ($admin == "ROLE_ADMIN") {
                return $this->redirectToRoute('app_admin');
            } else {
                // on recupère les dates de peremption en assignant des valeurs en fonction de la date de peremption
                $peremption->getPeremption($user);

                $product_rep = $doctrine->getRepository(Product::class);
                //Determination de la durée du warning
                $days = $user->getPeremptionWarning();
                $soon = new DateTime();
                $soon->modify('+ ' . $days . ' days');
                //On  recueille les datas pour le chart au moyen du service ChartsService
                $weightByCategory = $charts->getCategoriesForCharts($user);

                return $this->render('main/index.html.twig', [
                    'perime' => $product_rep->findProductByDate(new DateTime(), new DateTime('1970-01-01'), $user),
                    'soon_perime' => $product_rep->findProductByDate($soon, new DateTime(), $user),
                    'chart' => $weightByCategory,
                ]);
            }
        }
    }
}
