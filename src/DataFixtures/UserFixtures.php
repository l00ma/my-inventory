<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    public const USERS = [
        [
            'email' => 'user1@user.fr',
            'username' => 'Admin',
            'role' => 'ROLE_ADMIN',
            'peremption' => '10'
        ],
        [
            'email' => 'user2@user.fr',
            'username' => 'Fred',
            'role' => '',
            'peremption' => '100'
        ],
        [
            'email' => 'user3@user.fr',
            'username' => 'Thomas',
            'role' => '',
            'peremption' => '60'
        ],
        [
            'email' => 'user4@user.fr',
            'username' => 'Toto',
            'role' => '',
            'peremption' => '50'
        ],
    ];

    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $count = 0;
        foreach (self::USERS as $userItem) {
            $user = new User();
            $user->setEmail($userItem['email']);
            $user->setName($userItem['username']);
            $user->setPeremptionWarning($userItem['peremption']);
            $user->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                'password'
            ));
            $user->setRoles([$userItem['role']]);
            $maxValue = (count(CurrencyFixtures::CURRENCY)) - 1;
            $user->setCurrency($this->getReference('currency_' . rand(0, $maxValue)));

            $manager->persist($user);
            $this->addReference('user_' . $count, $user);
            $count++;
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [CurrencyFixtures::class];
    }
}
