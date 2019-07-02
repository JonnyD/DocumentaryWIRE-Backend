<?php

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;
use App\Enum\UserStatus;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

class UserFixtures extends Fixture
{
    private $encoder;


    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user1 = $this->createUser('user1', 'John', 'Smith', 'ROLE_ADMIN');
        $user2 = $this->createUser('user2', 'Sarah', 'McCarthy', 'ROLE_USER');
        $user3 = $this->createUser('user3', 'Andrew', 'Walsh', 'ROLE_USER');

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);
        $manager->flush();

        $this->createReference($user1);
        $this->createReference($user2);
        $this->createReference($user3);
    }

    /**
     * @param string $username
     * @param string $role
     * @return User
     */
    private function createUser(string $username, string $firstName, string $lastName, string $role)
    {
        $user = new User();
        //$user->setUsername($username);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($username . "@email.com");
        $user->setRoles([$role]);
        $user->setStatus(UserStatus::ACTIVE);
        $encodedPass = $this->encoder->encodePassword($user, 'pass');
        $user->setPassword($encodedPass);
        return $user;
    }

    /**
     * @param User $user
     */
    private function createReference(User $user)
    {
        $this->addReference('user.'.$user->getUsername(), $user);
    }
}