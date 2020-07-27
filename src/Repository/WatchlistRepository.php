<?php

namespace App\Repository;

use App\Criteria\WatchlistCriteria;
use App\Entity\Watchlist;
use App\Enum\Sync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Watchlist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Watchlist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Watchlist[]    findAll()
 * @method Watchlist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WatchlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Watchlist::class);
    }

    // /**
    //  * @return Watchlist[] Returns an array of Watchlist objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Watchlist
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param WatchlistCriteria $criteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findWatchlistByCriteria(WatchlistCriteria $criteria)
    {
        $criteria->setLimit(1);
        $qb = $this->findWatchlistsByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param WatchlistCriteria $criteria
     * @return ArrayCollection|Watchlist[]
     */
    public function findWatchlistsByCriteria(WatchlistCriteria $criteria)
    {
        $qb = $this->findWatchlistsByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $query->useResultCache(true, 3600, 'my_region')
            ->useQueryCache(true);
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param WatchlistCriteria $criteria
     * @return QueryBuilder
     */
    public function findWatchlistsByCriteriaQueryBuilder(WatchlistCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('watchlist')
            ->from('App\Entity\Watchlist', 'watchlist');

        if ($criteria->getUser()) {
            $qb->andWhere('watchlist.user = :user')
                ->setParameter('user', $criteria->getUser());
        }

        if ($criteria->getDocumentary()) {
            $qb->andWhere('watchlist.documentary = :documentary')
                ->setParameter('documentary', $criteria->getDocumentary());
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
     * @param Watchlist $watchlist
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Watchlist $watchlist, bool $sync)
    {
        $this->getEntityManager()->persist($watchlist);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
