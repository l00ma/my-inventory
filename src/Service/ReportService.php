<?php

namespace App\Service;

class ReportService
{

    public function getReport(array $products): array
    {
        // filtre les produits et calcule le poids total + prix total
        $totalWeight = $totalPrice = 0;
        if ($products) {
            foreach ($products as $product) {
                $untWeight = (int) $product->getUWeight();
                $untPrice = (int) $product->getPrice();
                $quantity = (int) $product->getQuantity();
                //le poids sera en grammes
                $totalWeight = $totalWeight + ($untWeight * $quantity);
                //le prix sera en cents
                $totalPrice = $totalPrice + ($untPrice * $quantity);
            }
        }
        //conversion du poids total de grammes à kilos
        $totalWeight = $totalWeight / 1000;
        $totalWeight = number_format($totalWeight, ((int) $totalWeight == $totalWeight ? 0 : 2), '.', ',');
        //conversion du prix total de cents à devise
        $totalPrice = $totalPrice / 100;
        $totalPrice = number_format($totalPrice, ((int) $totalPrice == $totalPrice ? 0 : 2), '.', ',');
        return array(strval($totalWeight), strval($totalPrice));
    }
}
