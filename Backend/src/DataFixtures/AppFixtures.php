<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setPseudo('chauffeur');
        $user->setEmail('chauffeur@ecoride.com');
        $user->setRoles(['ROLE_USER']);
        $user->setIsConducteur(true);
        $user->setIsPassager(false);


        if (method_exists($user, 'setCredits')) {
            $user->setCredits(20);
        }

        $hashedPassword = $this->hasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $manager->persist($user);
        $manager->flush();
    }
}
