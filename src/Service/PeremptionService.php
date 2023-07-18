<?php

namespace App\Service;

use DateTime;
use App\Entity\User;

class PeremptionService
{

    public function getPeremption(User $user): void
    {
        // filtre les produits par products
        $today = new DateTime();
        $products = $user->getProducts();
        if ($products) {
            foreach ($products as $product) {
                $interval = $today->diff($product->getLimitDate());
                $diff = (int) $interval->format('%r%a');
                // calcul du nbre de jour avant peremption
                $product->setPeremptionTime($diff);
                $warningDate = $user->getPeremptionWarning();
                // Produits perimés
                if ($diff <= 0) {
                    $product->setPeremptionAlert('0');
                    $product->setPeremptionCss('#ff6363');
                    // Produits qui seront périmés dans les x jours a venir
                } elseif ($diff <= (int)$warningDate) {
                    // echelonnage du fond rouge en fonction de la durée de warning
                    $color = 114 + round((int)$diff * (140 / (int)$warningDate - 0.05));
                    // conversion de color decimale en hex
                    $hexColor = str_pad(dechex($color), 2, "0", STR_PAD_LEFT);
                    // assignation du produit
                    $product->setPeremptionAlert('1');
                    $product->setPeremptionCss('#ff' . $hexColor . $hexColor);
                } else {
                    $product->setPeremptionAlert('2');
                }
            }
        }
    }
}
