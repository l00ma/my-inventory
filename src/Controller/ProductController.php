<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\PeremptionService;
use App\Service\PhotoService;
use App\Service\ReportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/main/product')]
class ProductController extends AbstractController
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

    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, ReportService $reportService, PeremptionService $peremption): Response
    {
        //on recupère la liste des produits de l'utilisateur
        $products = $productRepository->findAllProductByDate($this->getUser());
        //on recupère les poids et prix totaux que représentent des produits séléctionnés
        $report = $reportService->getReport($this->getUser(), $products);
        // on recupère les dates de peremption en assignant des valeurs en fonction de la date de peremption
        $peremption->getPeremption($this->getUser());

        return $this->render('product/index.html.twig', [
            'report' => $report,
            'products' => $products,
        ]);
    }

    #[Route('/new/{id<\d+>?}', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository, PhotoService $newPhoto, ?int $id): Response
    {
        $product = new Product();

        //si il y un id, on doit cloner le produit correspondant à l'id
        if ($id) {
            // en cas d'id inexistante ou n'appartenant pas au user courant, on declare une erreur 404 
            $cloned = $productRepository->find($id);
            if (is_null($cloned) || $cloned->getUser() !== $this->getUser()) {
                throw $this->createNotFoundException('The product does not exist');
            }
            //clonage du produit
            $product->setCategory($cloned->getCategory());
            $product->setBrand($cloned->getBrand());
            $product->setName($cloned->getName());
            $product->setUWeight($cloned->getUWeight());
            $product->setLocation($cloned->getLocation());
            $product->setRemark($cloned->getRemark());
        }

        // generation du formulaire
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // recuperation de l'image
            $photo = $form['image']->getData();
            // si il y a une image, on la sauve dans BDD et repertoire images
            if ($photo) {
                $newPhoto->addPhoto($product, $photo);
            }
            $id = $this->getUser();
            $product->setUser($id);
            $productRepository->save($product, true);
            $this->addFlash('success', 'Product successfully created');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id<\d+>}', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository, PhotoService $modifyPhoto, int $id): Response
    {
        // en cas d'id n'appartenant pas au user courant, on declare une erreur 404 
        if ($product->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('The product does not exist');
        }
        //on genere le formulaire
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //action si click sur BACK
            if ($form->getClickedButton() && 'back' === $form->getClickedButton()->getName()) {
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }
            //action si click sur SAVE
            if ($form->getClickedButton() && 'save' === $form->getClickedButton()->getName()) {
                // recuperation de l'image
                $photo = $form['image']->getData();
                // si il y a une image a ajouter ou a modifier, on la sauve dans BDD et repertoire images
                if ($photo) {
                    $modifyPhoto->addPhoto($product, $photo);
                }
                $productRepository->save($product, true);
                $this->addFlash('success', 'Product successfully saved');

                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }
            //action si click sur DELETE
            if ($form->getClickedButton() && 'delete' === $form->getClickedButton()->getName()) {
                $token = $request->request->get('_token');
                return $this->redirectToRoute('app_product_delete', [
                    'id' => $id,
                    '_token' => $token
                ], 307);
            }
        }
        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id<\d+>}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        // en cas d'id n'appartenant pas au user courant, on declare une erreur 404 
        if ($product->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('The product does not exist');
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $old_image = $product->getPhoto();
            if ($old_image) {
                $file_to_delete = $this->getParameter('images_directory') . '/' . $old_image->getName();
                if ($file_to_delete) {
                    unlink($file_to_delete);
                }
            }
            $productRepository->remove($product, true);
            $this->addFlash('success', 'Product successfully deleted');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search', name: 'app_product_search', methods: ['GET'])]
    public function searchResult(ProductRepository $productRepository, ReportService $reportService, PeremptionService $peremption, Request $request): Response
    {
        //on récupère la valeur de la query
        $query = $request->query->get('query');

        if (strlen($query) < 3) {
            $this->addFlash('error', 'Search must contains at least 3 characters');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }
        // on récupère les produits qui correspondent à la recherche
        $products = $productRepository->findSearchQuery(
            $this->getUser(),
            $query
        );
        //on recupère les poids et prix totaux que représentent des produits séléctionnés
        $report = $reportService->getReport($this->getUser(), $products);
        // on recupère les dates de peremption en assignant des valeurs en fonction de la date de peremption
        $peremption->getPeremption($this->getUser());


        return $this->render('product/searchresult.html.twig', [
            'query' => $query,
            'report' => $report,
            'products' => $products
        ]);
    }

    #[Route('/scan', name: 'app_product_scan', methods: ['GET'])]
    public function scan(ProductRepository $productRepository, ReportService $reportService, PeremptionService $peremption): Response
    {
        //on recupère la liste des produits de l'utilisateur
        $products = $productRepository->findAllProductByDate($this->getUser());
        //on recupère les poids et prix totaux que représentent des produits séléctionnés
        $report = $reportService->getReport($this->getUser(), $products);
        // on recupère les dates de peremption en assignant des valeurs en fonction de la date de peremption
        $peremption->getPeremption($this->getUser());

        return $this->render('product/scan.html.twig', [
            'report' => $report,
            'products' => $products,
        ]);
    }
}
