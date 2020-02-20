<?php

namespace App\Repository;

use App\Entity\Google;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GoogleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
