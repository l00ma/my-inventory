<?php

namespace App\Service;

use DateTime;
use App\Entity\User;

class PeremptionService
{

    public function getPeremption(User $user): void
    {
        // filtre les produits par dates
        $now = new DateTime();
        $dates = $user->getProducts();
        if ($dates) {
            foreach ($dates as $product_date) {
                $interval = $now->diff($product_date->getLimitDate());
                $diff = (int) $interval->format('%r%a');
                // calcul du nbre de jour avant peremption
                $product_date->setPeremptionTime($diff);
                $warningDate = $user->getPeremptionWarning();
                // Produits perimés
                if ($diff <= 0) {
                    $product_date->setPeremptionAlert('0');
                    $product_date->setPeremptionCss('#ff6363');
                    // Produits qui seront périmés dans les x jours a venir
                } elseif ($diff <= (int)$warningDate) {
                    // echelonnage du fond rouge en fonction de la durée de warning
                    $color = 114 + round((int)$diff * (140 / (int)$warningDate - 0.05));
                    // conversion de color decimale en hex
                    $hex = str_pad(dechex($color), 2, "0", STR_PAD_LEFT);
                    // assignation du produit
                    $product_date->setPeremptionAlert('1');
                    $product_date->setPeremptionCss('#ff' . $hex . $hex);
                }
            }
        }
    }
}
