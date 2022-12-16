<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Photo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PhotoService
{

    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        define('IMAGE_WIDTH', 400);
    }

    public function addPhoto(Product $product, $photo): void
    {
        //on recupere l'extention de l'image en minuscule
        $extention = strtolower($photo->guessExtension());
        $fichier = md5(uniqid()) . '.' . $extention;

        $old_image = $product->getPhoto();

        if ($old_image) {
            $file_to_delete = $this->params->get('images_directory') . '/' . $old_image->getName();
            unlink($file_to_delete);
            $product->getPhoto()->setName($fichier);
        } else {
            $image_product = new Photo();
            $image_product->setName($fichier);
            $product->setPhoto($image_product);
        }

        if ($extention == 'jpg' || $extention == 'jpeg') {
            $photo = imagecreatefromjpeg($photo);
            $photo = imagescale($photo, IMAGE_WIDTH, -1);
            imagejpeg($photo, $this->params->get('images_directory') . '/' . $fichier);
        }

        if ($extention == 'png') {
            $photo = imagecreatefrompng($photo);
            $photo = imagescale($photo, IMAGE_WIDTH, -1);
            imagepng($photo, $this->params->get('images_directory') . '/' . $fichier);
        }
    }
}
