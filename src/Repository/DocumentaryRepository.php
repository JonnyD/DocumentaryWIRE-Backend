<?php

namespace App\Repository;

use App\Criteria\DocumentaryCriteria;
use App\Entity\Documentary;
use App\Enum\DurationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Documentary|null find($id, $lockMode = null, $lockVersion = null)
 * @method Documentary|null findOneBy(array $criteria, array $orderBy = null)
 * @method Documentary[]    findAll()
 * @method Documentary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentaryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Documentary::class);
    }

    // /**
    //  * @return Documentary[] Returns an array of Documentary objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Documentary
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return mixed
     */
    public function findYears()
    {
        $rsm = new ResultSetMapping();

        $sql = "SELECT DISTINCT(year_from) FROM documentary WHERE year_from IS NOT NULL ORDER BY year_from";

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * @param DocumentaryCriteria $criteria
     * @return Documentary
     */
    public function findDocumentaryByCriteria(DocumentaryCriteria $criteria)
    {
        $criteria->setLimit(1);
        $qb = $this->findDocumentariesByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param DocumentaryCriteria $criteria
     * @return ArrayCollection|Documentary[]
     */
    public function findDocumentariesByCriteria(DocumentaryCriteria $criteria)
    {
        $qb = $this->findDocumentariesByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $query->useResultCache(true, 3600, 'my_region')
            ->useQueryCache(true);
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param DocumentaryCriteria $criteria
     * @return QueryBuilder
     */
    public function findDocumentariesByCriteriaQueryBuilder(DocumentaryCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('documentary')
            ->from('App\Entity\Documentary', 'documentary');

        if ($criteria->isFeatured() != null) {
            $qb->andWhere('documentary.featured = :featured')
                ->setParameter('featured', $criteria->isFeatured());
        }

        if ($criteria->getStatus()) {
            $qb->andWhere('documentary.status = :status')
                ->setParameter('status', $criteria->getStatus());
        }

        if ($criteria->getCategory()) {
            $qb->andWhere('documentary.category = :category')
                ->setParameter('category', $criteria->getCategory());
        }

        if ($criteria->getVideoSource()) {
            $qb->leftJoin('documentary.documentaryVideoSources', 'documentaryVideoSources')
                ->andWhere('documentaryVideoSources.videoSource = :videoSource')
                ->setParameter('videoSource', $criteria->getVideoSource());
        }

        if ($criteria->getAddedBy()) {
            $qb->andWhere('documentary.addedBy = :addedBy')
                ->setParameter('addedBy', $criteria->getAddedBy());
        }
        
        if ($criteria->getYear()) {
            $qb->andWhere('documentary.yearFrom = :yearFrom')
                ->setParameter('yearFrom', $criteria->getYear());
        }

        if ($criteria->getType()) {
            $qb->andWhere('documentary.type = :type')
                ->setParameter('type', $criteria->getType());
        }

        if ($criteria->getDuration()) {
            switch ($criteria->getDuration()) {
                case DurationType::LESS_THAN_4_MINUTES:
                    $qb->andWhere('documentary.length < :duration')
                        ->setParameter('duration', 4);
                    break;
                case DurationType::LESS_THAN_20_MINUTES:
                    $qb->andWhere('documentary.length < :duration')
                        ->setParameter('duration', 20);
                    break;
                case DurationType::GREATER_THAN_20_MINUTES:
                    $qb->andWhere('documentary.length > :duration')
                        ->setParameter('duration', 20);
                    break;
                case DurationType::GREATER_THAN_60_MINUTES:
                    $qb->andWhere('documentary.length > :duration')
                        ->setParameter('duration', 60);
                    break;
            }
        }

        if ($criteria->getSort()) {
            foreach ($criteria->getSort() as $column => $direction) {
                $qb->addOrderBy($qb->getRootAliases()[0] . '.' . $column, $direction);
            }
        }

        if ($criteria->getLimit()) {
            $qb->setMaxResults($criteria->getLimit());
        }

        return $qb;
    }

    /**
     * @param Documentary $documentary
     * @param bool $sync
     */
    public function save(Documentary $documentary, bool $sync = true)
    {
        $this->getEntityManager()->persist($documentary);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
