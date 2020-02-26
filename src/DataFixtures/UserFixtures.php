<?php

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;
use App\Enum\UserStatus;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

class UserFixtures extends Fixture implements ContainerAwareInterface
{
    const USER_MANAGER = 'fos_user.user_manager';

    /**
     * @var ContainerAwareInterface
     */
    private $container;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->userManager = $this->container->get(static::USER_MANAGER);
    }
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user1 = $this->createUser('user1', 'John', 'Smith', 'ROLE_ADMIN', '1c3fb568d51feb12a0038033890efb5367585af3a', new \DateTime(), true, new \DateTime('2020-01-01'));
        $user2 = $this->createUser('user2', 'Sarah', 'McCarthy', 'ROLE_USER', '2c3fb568d51feb12a0038033890efb5367585af3a', new \DateTime(), true, new \DateTime('2020-02-01'));
        $user3 = $this->createUser('user3', 'Andrew', 'Walsh', 'ROLE_USER', '3c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true, new \DateTime('2020-01-04'));
        $user4 = $this->createUser('user4', 'Anne', 'Keating', 'ROLE_USER', '4c3fb568d51feb12a0038033890efb5367585af3a',null, false, new \DateTime('2020-01-21'), new \DateTime("2020-01-01"));
        $user5 = $this->createUser('user5', 'Jerry', 'Carroll', 'ROLE_USER', '5c3fb568d51feb12a0038033890efb5367585af3a',null, false, new \DateTime('2020-02-21'),  new \DateTime());
        $user6 = $this->createUser('user6', 'Sarah', 'Kirwin', 'ROLE_USER', '6c3fb568d51feb12a00380new33890efb5367585af3a',null, false);
        $user7 = $this->createUser('user7', 'Carolyn', 'Smith', 'ROLE_USER', '7c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user8 = $this->createUser('user8', 'Harold', 'Daniels', 'ROLE_USER', '8c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user9 = $this->createUser('user9', 'Marianna', 'Hernandez', 'ROLE_USER', '9c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user10 = $this->createUser('user10', 'Kyle', 'Bockman', 'ROLE_USER', '10c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user11 = $this->createUser('user11', 'Mary', 'Baer', 'ROLE_USER', '11c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user12 = $this->createUser('user12', 'William', 'Gold', 'ROLE_USER', '12c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user13 = $this->createUser('user13', 'Willy', 'Greer', 'ROLE_USER', '13c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user14 = $this->createUser('user14', 'Samuel', 'Guill', 'ROLE_USER', '14c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user15 = $this->createUser('user15', 'Michael', 'Ball', 'ROLE_USER', '15c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user16 = $this->createUser('user16', 'Henry', 'Lamb', 'ROLE_USER', '16c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user17 = $this->createUser('user17', 'Elaine', 'Clothier', 'ROLE_USER', '17c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user18 = $this->createUser('user18', 'Scott', 'Anders', 'ROLE_USER', '18c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);
        $user19 = $this->createUser('user19', 'Kathleen', 'Sims', 'ROLE_USER', '19c3fb568d51feb12a0038033890efb5367585af3a',new \DateTime(), true);

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);
        $manager->persist($user4);
        $manager->persist($user5);
        $manager->persist($user6);
        $manager->persist($user7);
        $manager->persist($user8);
        $manager->persist($user9);
        $manager->persist($user10);
        $manager->persist($user11);
        $manager->persist($user12);
        $manager->persist($user13);
        $manager->persist($user14);
        $manager->persist($user15);
        $manager->persist($user16);
        $manager->persist($user17);
        $manager->persist($user18);
        $manager->persist($user19);
        $manager->flush();

        $this->createReference($user1);
        $this->createReference($user2);
        $this->createReference($user3);
        $this->createReference($user4);
        $this->createReference($user5);
        $this->createReference($user6);
        $this->createReference($user7);
        $this->createReference($user8);
        $this->createReference($user9);
        $this->createReference($user10);
        $this->createReference($user11);
        $this->createReference($user12);
        $this->createReference($user13);
        $this->createReference($user14);
        $this->createReference($user15);
        $this->createReference($user16);
        $this->createReference($user17);
        $this->createReference($user18);
        $this->createReference($user19);
    }

    /**
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param string $confirmationToken
     * @param \DateTime|null $activatedAt
     * @param bool $enabled
     * @param \DateTime|null $lastLogin
     * @param \DateTime|null $passwordRequestedAt
     * @return User
     */
    private function createUser(
        string $username,
        string $firstName,
        string $lastName,
        string $role,
        string $confirmationToken,
        \DateTime $activatedAt = null,
        bool $enabled,
        \DateTime $lastLogin = null,
        \DateTime $passwordRequestedAt = null)
    {
        $user = new User();

        $user->setUsername($username);
        $user->setName($firstName . ' ' . $lastName);
        $user->setEmail($username . "@email.com");
        $user->setRoles([$role]);
        $user->setPlainPassword('password');
        $user->setConfirmationToken($confirmationToken);
        $user->setActivatedAt($activatedAt);
        $user->setEnabled($enabled);
        $user->setLastLogin($lastLogin);
        $user->setResetKey($confirmationToken);
        $user->setAvatar("0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg");
        $user->setPasswordRequestedAt($passwordRequestedAt);
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