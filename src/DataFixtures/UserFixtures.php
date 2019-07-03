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
        $user1 = $this->createUser('user1', 'John', 'Smith', 'ROLE_ADMIN', new \DateTime());
        $user2 = $this->createUser('user2', 'Sarah', 'McCarthy', 'ROLE_USER', new \DateTime());
        $user3 = $this->createUser('user3', 'Andrew', 'Walsh', 'ROLE_USER', new \DateTime());
        $user4 = $this->createUser('user4', 'Anne', 'Keating', 'ROLE_USER', null);
        $user5 = $this->createUser('user5', 'Jerry', 'Carroll', 'ROLE_USER', null);
        $user6 = $this->createUser('user6', 'Sarah', 'Kirwin', 'ROLE_USER', null);

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);
        $manager->persist($user4);
        $manager->persist($user5);
        $manager->persist($user6);
        $manager->flush();

        $this->createReference($user1);
        $this->createReference($user2);
        $this->createReference($user3);
        $this->createReference($user4);
        $this->createReference($user5);
        $this->createReference($user6);
    }

    /**
     * @param string $username
     * @param string $role
     * @return User
     */
    private function createUser(string $username, string $firstName, string $lastName, string $role, \DateTime $activatedAt = null)
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
        $activationKey = sha1(mt_rand(10000,99999).time().$user->getEmail());
        $user->setActivationKey($activationKey);
        $user->setActivatedAt($activatedAt);
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