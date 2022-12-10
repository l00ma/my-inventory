<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Photo;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
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
    public function index(ProductRepository $productRepository): Response
    {
        // faire tri sur dates ici

        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $image_product = new Photo();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form['image']->getData();
            if ($photo) {
                $fichier = md5(uniqid()) . '.' . $photo->guessExtension();
                $photo->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                $image_product->setName($fichier);
                $product->setPhoto($image_product);
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
    public function edit(Request $request, Product $product, ProductRepository $productRepository, ?Photo $photo, int $id): Response
    {
        $user_ids = $this->getUser()->getProducts();

        foreach ($user_ids as $ids) {
            if ($ids->getId() == $id) {
                $form = $this->createForm(ProductType::class, $product);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    if ($form->getClickedButton() && 'back' === $form->getClickedButton()->getName()) {
                        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
                    }

                    if ($form->getClickedButton() && 'save' === $form->getClickedButton()->getName()) {

                        $if_photo = $form['image']->getData();
                        if ($if_photo) {
                            $fichier = md5(uniqid()) . '.' . $if_photo->guessExtension();
                            $image_name = $product->getPhoto();

                            if ($image_name) {
                                $file_to_delete = $this->getParameter('images_directory') . '/' . $image_name->getName();
                                unlink($file_to_delete);
                                $product->getPhoto()->setName($fichier);
                            } else {
                                $image_product = new Photo();
                                $image_product->setName($fichier);
                                $product->setPhoto($image_product);
                            }

                            $if_photo->move(
                                $this->getParameter('images_directory'),
                                $fichier
                            );
                        }

                        $productRepository->save($product, true);
                        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
                    }

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
            $image_name = $product->getPhoto();
            if ($image_name) {
                $file_to_delete = $this->getParameter('images_directory') . '/' . $image_name->getName();
                if ($file_to_delete) {
                    unlink($file_to_delete);
                }
            }
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
