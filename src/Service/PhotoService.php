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

    public function addPhoto(Product $product, $photoFile): void
    {
        //on recupere l'extention de l'image en minuscule
        $extention = strtolower($photoFile->guessExtension());
        $fileName = md5(uniqid()) . '.' . $extention;

        $oldPhoto = $product->getPhoto();

        if ($oldPhoto) {
            $oldPhotoFile = $this->params->get('images_directory') . '/' . $oldPhoto->getName();
            unlink($oldPhotoFile);
            $product->getPhoto()->setName($fileName);
        } else {
            $photo = new Photo();
            $photo->setName($fileName);
            $product->setPhoto($photo);
        }

        if ($extention == 'jpg' || $extention == 'jpeg') {
            $photoFile = imagecreatefromjpeg($photoFile);
            $photoFile = imagescale($photoFile, IMAGE_WIDTH, -1);
            imagejpeg($photoFile, $this->params->get('images_directory') . '/' . $fileName);
        }

        if ($extention == 'png') {
            $photoFile = imagecreatefrompng($photoFile);
            $photoFile = imagescale($photoFile, IMAGE_WIDTH, -1);
            imagepng($photoFile, $this->params->get('images_directory') . '/' . $fileName);
        }
    }
}
