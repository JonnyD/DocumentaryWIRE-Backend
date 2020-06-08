<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Enum\Sync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
     * @param Movie $movie
     * @param string $sync
     */
    public function save(Movie $movie, string $sync = Sync::YES)
    {
        $this->getEntityManager()->persist($movie);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param Movie $movie
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(Movie $movie, string $sync = Sync::YES)
    {
        $this->getEntityManager()->remove($movie);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }
}
