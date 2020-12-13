<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('dummy');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'azerty'
        ));
        $manager->persist($user);

        $admin = new User();
        $admin->setUsername('pdasilva');
        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        $admin->setPassword($this->passwordEncoder->encodePassword(
            $admin,
            'Pierre93*'
        ));
        $manager->persist($admin);

        $manager->flush();

        $this->addReference('user-dummy', $user);
        $this->addReference('user-admin', $admin);
    }
}
