<?php

namespace DW\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use DW\UserBundle\Entity\Role;

class RoleFixtures extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userRole = $this->createRole('user', 'ROLE_USER');
        $adminRole = $this->createRole('admin', 'ROLE_ADMIN');

        $manager->persist($userRole);
        $manager->persist($adminRole);
        $manager->flush();

        $this->createReference($userRole);
        $this->createReference($adminRole);
    }

    /**
     * @param string $name
     * @param string $roleName
     * @return Role
     */
    private function createRole(string $name, string $roleName)
    {
        $role = new Role();
        $role->setName($name);
        $role->setRole($roleName);
        return $role;
    }

    /**
     * @param Role $role
     */
    private function createReference(Role $role)
    {
        $this->addReference('role.'.$role->getName(), $role);
    }
}