<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\PeremptionService;
use App\Service\NewPhotoService;
use App\Service\PhotoService;
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
    public function index(ProductRepository $productRepository, PeremptionService $peremption): Response
    {
        // on recupère les dates de peremption en assignant des valeurs en fonction de la date de peremption
        $peremption->getPeremption($this->getUser());

        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository, NewPhotoService $newPhoto): Response
    {
        $product = new Product();
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

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product, ProductRepository $productRepository, int $id): Response
    {
        $user_ids = $this->getUser()->getProducts();
        foreach ($user_ids as $ids) {
            if ($ids->getId() == $id) {
                return $this->render('product/show.html.twig', [
                    'product' => $product,
                ]);
            }
        }
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository, PhotoService $modifyPhoto, int $id): Response
    {
        //on empeche l'acces aux produts des autres utilisateurs
        $user_ids = $this->getUser()->getProducts();
        foreach ($user_ids as $ids) {
            if ($ids->getId() == $id) {
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
                        // si il y a une image, on la sauve dans BDD et repertoire images
                        if ($photo) {
                            $modifyPhoto->addPhoto($product, $photo);
                        }

                        $productRepository->save($product, true);

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
        }
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
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
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
