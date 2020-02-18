<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MovieRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
     * @param Movie $movie
     * @param bool $sync
     */
    public function save(Movie $movie, bool $sync = true)
    {
        $this->getEntityManager()->persist($movie);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param Movie $movie
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(Movie $movie, $sync = true)
    {
        $this->getEntityManager()->remove($movie);
        if ($sync) {
            $this->flush();
        }
    }
}
