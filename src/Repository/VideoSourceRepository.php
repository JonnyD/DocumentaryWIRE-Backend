<?php

namespace App\Repository;

use App\Criteria\VideoSourceCriteria;
use App\Entity\VideoSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VideoSource|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoSource|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoSource[]    findAll()
 * @method VideoSource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoSourceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VideoSource::class);
    }

    // /**
    //  * @return VideoSource[] Returns an array of VideoSource objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VideoSource
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param VideoSourceCriteria $criteria
     * @return ArrayCollection|VideoSource[]
     */
    public function findVideoSourcesByCriteria(VideoSourceCriteria $criteria)
    {
        $qb = $this->findVideoSourcesByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param VideoSourceCriteria
     * @return QueryBuilder
     */
    public function findVideoSourcesByCriteriaQueryBuilder(VideoSourceCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('videoSource')
            ->from('App\Entity\VideoSource', 'videoSource');

        if (!empty($criteria->isEnabled())) {
            $qb->andWhere('videoSource.enabled = :enabled')
                ->setParameter('enabled', $criteria->isEnabled());
        }

        if (!empty($criteria->isEmbedAllowed())) {
            $qb->andWhere('videoSource.embed = :embed')
                ->setParameter('embed', $criteria->isEmbedAllowed());
        }

        return $qb;
    }

}
