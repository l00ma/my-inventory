<?php

namespace App\Controller;

use App\Service\PeremptionService;
use App\Service\ChartsService;
use DateTime;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/main', name: 'app_main')]
    public function index(PeremptionService $peremption, ProductRepository $productRepository): Response
    {
        $roles = $this->getUser()->getRoles();
        $user = $this->getUser();

        foreach ($roles as $role) {
            if ($role == "ROLE_ADMIN") {
                return $this->redirectToRoute('app_admin');
            } else {
                // on recupère les dates de peremption en assignant des valeurs en fonction de la date de peremption
                $peremption->getPeremption($user);

                //Determination de la durée du warning
                $days = $user->getPeremptionWarning();
                $soon = new DateTime();
                $soon->modify('+ ' . $days . ' days');

                return $this->render('main/index.html.twig', [
                    'perime' => $productRepository->findProductByDate(new DateTime('now -1 day'), new DateTime('1970-01-01'), $user),
                    'soon_perime' => $productRepository->findProductByDate($soon, new DateTime('now -1 day'), $user),
                ]);
            }
        }
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/chart', name: 'app_chart')]
    public function chart(ChartsService $charts): JsonResponse
    {
        $user = $this->getUser();
        $weightByCategory = $charts->getCategoriesForCharts($user);
        return $this->json($weightByCategory);
    }
}
