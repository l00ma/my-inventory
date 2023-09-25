<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixtures extends Fixture
{
    public const CURRENCY = [
        'EUR',
        'GPB',
        'USD',
    ];

    public function load(ObjectManager $manager): void
    {
            foreach (self::CURRENCY as $cur) {
                $currency = new Currency();
                $currency->setName($cur);
                $manager->persist($currency);
                $this->addReference('currency_' . array_search($cur, self::CURRENCY), $currency);
            }
            $manager->flush();
        }
}
