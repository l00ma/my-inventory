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
                // Produits perimés
                if ($diff <= 0) {
                    $product_date->setPeremptionAlert('0');
                    // Produits qui seront périmés dans le mois a venir
                } elseif ($diff <= 31) {
                    $product_date->setPeremptionAlert('1');
                    // Produits qui seront périmés dans les 3 mois a venir
                } elseif ($diff <= 92) {
                    $product_date->setPeremptionAlert('2');
                }
            }
        }
    }
}
