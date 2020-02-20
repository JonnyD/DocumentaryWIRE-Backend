<?php

namespace App\Repository;

use App\Entity\Facebook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FacebookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facebook::class);
    }

    /**
     * @param Facebook $facebook
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Facebook $facebook, bool $sync = true)
    {
        $this->getEntityManager()->persist($facebook);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
