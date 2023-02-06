<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CategoryRepository;

class ChartsService
{

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categories = $categoryRepository;
    }

    public function getCategoriesForCharts(User $user): array
    {
        $results = array();
        // Pour chaque catégorie, on calcul le poids total des produits du user
        $allCategories = $this->categories->findAll();
        if ($allCategories) {
            foreach ($allCategories as $uniqueCategory) {
                $allProductsByCategory = $uniqueCategory->getProducts();
                if ($allProductsByCategory) {
                    $totalweight = 0;
                    foreach ($allProductsByCategory as $uniqueProductByCategory) {
                        if ($uniqueProductByCategory->getUser() == $user) {
                            $unt_weight = (int) $uniqueProductByCategory->getUWeight();
                            $quty = (int) $uniqueProductByCategory->getQuantity();
                            //le poids sera en grammes
                            $totalweight = $totalweight + ($unt_weight * $quty);
                        }
                    }
                    //Poids en Kg
                    $totalweight = $totalweight / 1000;
                    if ($totalweight != 0) {
                        $totalweight = number_format($totalweight, ((int) $totalweight == $totalweight ? 0 : 3), '.', ',');
                        $results[$uniqueCategory->getName()] = strval($totalweight);
                    }
                }
            }
        }
        return ($results);
    }
}
