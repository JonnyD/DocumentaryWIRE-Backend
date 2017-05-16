<?php

namespace DW\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DW\UserBundle\Entity\Role;
use DW\UserBundle\Entity\User;
use DW\UserBundle\Enum\UserStatus;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserFixtures extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');

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
     * @return User
     */
    private function createUser(string $username)
    {
        $userRole = $this->getRole("user");

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username . "@email.com");
        $user->addRole($userRole);
        $user->setStatus(UserStatus::ACTIVE);
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $encodedPass = $encoder->encodePassword('pass', $user->getSalt());
        $user->setPassword($encodedPass);
        return $user;
    }

    /**
     * @param string $name
     * @return Role
     */
    private function getRole(string $name)
    {
        return $this->getReference('role.'.$name);
    }

    /**
     * @param User $user
     */
    private function createReference(User $user)
    {
        $this->addReference('user.'.$user->getUsername(), $user);
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            RoleFixtures::class
        ];
    }
}