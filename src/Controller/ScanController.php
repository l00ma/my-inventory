<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\PeremptionService;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScanController extends AbstractController
{
        #[Route('/scan', name: 'app_scan')]
        public function scan(Request $request): Response
        {
            return $this->render('scan/index.html.twig');
        }

        #[Route('/codebar', name: 'app_codebar', methods: ['GET'])]
        public function getCodeBar(Request $request): Response
        {
            $query = $request->query->get('query');
            dd($query);
            //on recupère la liste des produits de l'utilisateur
    
            return $this->render('scan/result.html.twig');
        }
}
