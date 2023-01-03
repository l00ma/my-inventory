<?php

namespace App\Service;

use App\Entity\User;

class ReportService
{

    public function getReport(User $user): array
    {
        // filtre les produits et calcule le poids total + prix total
        $products = $user->getProducts();
        $totalweight = $totalprice = 0;
        if ($products) {
            foreach ($products as $product) {
                $unt_weight = (int) $product->getUWeight();
                $unt_price = (int) $product->getPrice();
                $quty = (int) $product->getQuantity();
                //le poids sera en grammes
                $totalweight = $totalweight + ($unt_weight * $quty);
                //le prix sera en cents
                $totalprice = $totalprice + ($unt_price * $quty);
            }
        }
        //conversion du poids total de grammes à kilos
        $totalweight = $totalweight / 1000;
        $totalweight = number_format($totalweight, ((int) $totalweight == $totalweight ? 0 : 2), '.', ',');
        //conversion du prix total de cents à devise
        $totalprice = $totalprice / 100;
        $totalprice = number_format($totalprice, ((int) $totalprice == $totalprice ? 0 : 2), '.', ',');
        return array(strval($totalweight), strval($totalprice));
    }
}
