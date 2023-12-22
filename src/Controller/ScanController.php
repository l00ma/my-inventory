<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\PeremptionService;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ScanController extends AbstractController
{

    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

        #[IsGranted('ROLE_USER')]
        #[Route('/scan', name: 'app_scan')]
        public function scan(Request $request): Response
        {
            return $this->render('scan/index.html.twig');
        }

        #[IsGranted('ROLE_USER')]
        #[Route('/codebar', name: 'app_codebar', methods: ['GET'])]
        public function getCodeBar(Request $request): JsonResponse
        {
            $query = $request->query->get('query');
            //on recupère les infos produit
            $response = $this->client->request(
                'GET',
                'https://world.openfoodfacts.net/api/v2/product/' . $query . '?fields=product_name,brands,image_front_url'
            );
            $content = $response->getContent();
           
            return $this->json($content);
        }
}
