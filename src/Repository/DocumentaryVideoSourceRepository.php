<?php

namespace App\Repository;

use App\Entity\DocumentaryVideoSource;
use App\Enum\Sync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DocumentaryVideoSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentaryVideoSource::class);
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(DocumentaryVideoSource $documentaryVideoSource, string $sync = Sync::YES)
    {
        $this->getEntityManager()->remove($documentaryVideoSource);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(DocumentaryVideoSource $documentaryVideoSource, string $sync = Sync::YES)
    {
        $this->getEntityManager()->persist($documentaryVideoSource);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}