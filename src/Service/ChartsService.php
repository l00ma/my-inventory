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
        $categories = $this->categories->findAll();
        if ($categories) {
            foreach ($categories as $category) {
                $productsByCategory = $category->getProducts();
                if ($productsByCategory) {
                    $totalWeight = 0;
                    foreach ($productsByCategory as $productByCategory) {
                        if ($productByCategory->getUser() == $user) {
                            $untWeight = (int) $productByCategory->getUWeight();
                            $quty = (int) $productByCategory->getQuantity();
                            //le poids sera en grammes
                            $totalWeight = $totalWeight + ($untWeight * $quty);
                        }
                    }
                    //Poids en Kg
                    $totalWeight = $totalWeight / 1000;
                    if ($totalWeight != 0) {
                        $totalWeight = number_format($totalWeight, ((int) $totalWeight == $totalWeight ? 0 : 3), '.', ',');
                        $results[$category->getName()] = strval($totalWeight);
                    }
                }
            }
        }
        return ($results);
    }
}
