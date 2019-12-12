<?php

namespace App\Repository;

use App\Entity\DocumentaryVideoSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DocumentaryVideoSourceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DocumentaryVideoSource::class);
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(DocumentaryVideoSource $documentaryVideoSource, bool $sync = true)
    {
        $this->getEntityManager()->remove($documentaryVideoSource);
        if ($sync) {
            $this->flush();
        }
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(DocumentaryVideoSource $documentaryVideoSource, $sync = true)
    {
        $this->getEntityManager()->persist($documentaryVideoSource);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}