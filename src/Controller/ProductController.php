<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Photo;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\Peremption;
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
        define('IMAGE_WIDTH', 400);
    }

    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, Peremption $peremption): Response
    {
        // on recupère les dates de peremption en assignant des valeurs en fonction de la date de peremption
        $peremption->getPeremption($this->getUser());

        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $image_product = new Photo();
        // generation du formulaire
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // recuperation de l'image
            $photo = $form['image']->getData();
            // si il y a une image, on la sauve dans BDD et repertoire images
            if ($photo) {
                // on recupère l'extension en minuscules
                $extention = strtolower($photo->guessExtension());
                $fichier = md5(uniqid()) . '.' . $extention;

                if ($extention == 'jpg' || $extention == 'jpeg') {
                    $photo = imagecreatefromjpeg($photo);
                    $photo = imagescale($photo, IMAGE_WIDTH, -1);
                    imagejpeg($photo, $this->getParameter('images_directory') . '/' . $fichier);
                }
                if ($extention == 'png') {
                    $photo = imagecreatefrompng($photo);
                    $photo = imagescale($photo, IMAGE_WIDTH, -1);
                    imagepng($photo, $this->getParameter('images_directory') . '/' . $fichier);
                }
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
    public function edit(Request $request, Product $product, ProductRepository $productRepository, int $id): Response
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
                        $if_photo = $form['image']->getData();
                        // si il y a une image, on la sauve dans BDD et repertoire images
                        if ($if_photo) {
                            //on recupere l'extention de l'image en minuscule
                            $extention = strtolower($if_photo->guessExtension());

                            $fichier = md5(uniqid()) . '.' . $extention;
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

                            if ($extention == 'jpg' || $extention == 'jpeg') {
                                $if_photo = imagecreatefromjpeg($if_photo);
                                $if_photo = imagescale($if_photo, IMAGE_WIDTH, -1);
                                imagejpeg($if_photo, $this->getParameter('images_directory') . '/' . $fichier);
                            }

                            if ($extention == 'png') {
                                $if_photo = imagecreatefrompng($if_photo);
                                $if_photo = imagescale($if_photo, IMAGE_WIDTH, -1);
                                imagepng($if_photo, $this->getParameter('images_directory') . '/' . $fichier);
                            }
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
