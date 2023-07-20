<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const CATEGORIES = [
        'starchy foods',
        'drink',
        'oil',
        'milk',
        'sausage',
        'can of vegetables',
        'can of fruits',
        'can of meat',
        'can of fish',
        'soup brick',
        'condiment',
        'coffee',
        'cream',
        'directly edible',
    ];

    public function load(ObjectManager $manager): void
    {
            foreach (self::CATEGORIES as $cat) {
                $category = new Category();
                $category->setName($cat);
                $manager->persist($category);
                $this->addReference('category_' . array_search($cat, self::CATEGORIES), $category);
            }
            $manager->flush();
        }
}
