<?php

namespace App\Repository;

use App\Entity\Google;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GoogleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Google::class);
    }

    /**
     * @param Google $google
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Google $google, bool $sync = true)
    {
        $this->getEntityManager()->persist($google);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
