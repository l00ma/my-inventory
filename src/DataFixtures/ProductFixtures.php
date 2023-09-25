<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public const PRODUCTS = [
        ['name' => 'Riz', 'brand' => 'Buitonis', 'u_weight' => '500'],
        ['name' => '1.5 liter bottle', 'brand' => 'Coca-cola', 'u_weight' => '1500'],
        ['name' => 'riz en sachet', 'brand' => 'Monti', 'u_weight' => '1000'],
        ['name' => 'Cocktail avec jus naturel', 'brand' => 'St Mamet', 'u_weight' => '425'],
        ['name' => 'Ratatouille', 'brand' => 'Fleur des champs', 'u_weight' => '750'],
        ['name' => 'Haricots verts très fins', 'brand' => 'Carrefour', 'u_weight' => '800'],
        ['name' => 'Haricots verts Extra-fins', 'brand' => 'Fleur des champs', 'u_weight' => '800'],
        ['name' => 'Petits pois et carottes', 'brand' => 'D\'aucy', 'u_weight' => '800'],
        ['name' => 'Petits pois et carottes', 'brand' => 'Fleur des champs', 'u_weight' => '800'],
        ['name' => 'Flageolets verts', 'brand' => 'Fleur des champs', 'u_weight' => '800'],
        ['name' => 'Petits pois extra-fins', 'brand' => 'Fleur des champs', 'u_weight' => '800'],
        ['name' => 'Poêlée parisienne', 'brand' => 'D\'aucy', 'u_weight' => '800'],
        ['name' => 'Petits pois extra-fins', 'brand' => 'D\'aucy', 'u_weight' => '800'],
        ['name' => 'Petits pois extra-fins', 'brand' => 'Bonduel', 'u_weight' => '200'],
        ['name' => 'Penne Rigate', 'brand' => 'Barilla', 'u_weight' => '800'],
        ['name' => 'Epinards', 'brand' => 'Carrefour', 'u_weight' => '795'],
        ['name' => 'Epinards', 'brand' => 'D\'aucy', 'u_weight' => '765'],
        ['name' => 'Champignons de Paris', 'brand' => 'Bonduel', 'u_weight' => '200'],
        ['name' => 'Fusilli', 'brand' => 'Barilla', 'u_weight' => '800'],
        ['name' => 'Lentilles', 'brand' => 'Fleur des champs', 'u_weight' => '800'],
    ];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $maxCategoryValue = (count(CategoryFixtures::CATEGORIES)) -1 ;
        $maxUserValue = (count(UserFixtures::USERS));

        for ($i = 0; $i < $maxUserValue; $i++) {
            if(!in_array("ROLE_ADMIN", ($this->getReference('user_' . $i))->getRoles())) {
                foreach (self::PRODUCTS as $productItem) {
                    $product = new Product();
                    $product->setName($productItem['name']);
                    $product->setBrand($productItem['brand']);
                    $product->setUWeight($productItem['u_weight']);
                    $product->setCategory($this->getReference('category_' . rand(0, $maxCategoryValue)));
                    $product->setUser($this->getReference('user_' . $i));
                    $product->setPrice($faker->randomNumber(3, true));
                    $product->setQuantity($faker->numberBetween(1, 4));
                    $product->setLimitDate($faker->dateTimeBetween('-5 weeks', '+2 years'));
                    $product->setLocation($faker->numberBetween(1, 8));
                    $product->setRemark($faker->sentence());
                    $manager->persist($product);
                }
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [CategoryFixtures::class, UserFixtures::class];
    }
}
