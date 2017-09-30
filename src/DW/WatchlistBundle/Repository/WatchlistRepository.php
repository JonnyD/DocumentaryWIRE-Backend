<?php

namespace DW\WatchlistBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use DW\WatchlistBundle\Criteria\WatchlistCriteria;
use DW\WatchlistBundle\Entity\Watchlist;

class WatchlistRepository extends EntityRepository
{
    /**
     * @param Watchlist $watchlist
     * @param bool $sync
     */
    public function save(Watchlist $watchlist, bool $sync = true)
    {
        $this->getEntityManager()->persist($watchlist);
        if ($sync) {
            $this->flush();
        }
    }

    /**
     * @param Watchlist $watchlist
     */
    public function remove(Watchlist $watchlist)
    {
        $this->getEntityManager()->remove($watchlist);
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param WatchlistCriteria $criteria
     * @return ArrayCollection|Watchlist[]
     */
    public function findAllByCriteria(WatchlistCriteria $criteria)
    {
        $qb = $this->findByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param WatchlistCriteria $criteria
     * @return Watchlist
     */
    public function findByCriteria(WatchlistCriteria $criteria)
    {
        $qb = $this->findByCriteriaQueryBuilder($criteria);
        $qb->setMaxResults(1);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param WatchlistCriteria $criteria
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findByCriteriaQueryBuilder(WatchlistCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('watchlist')
            ->from('DW\WatchlistBundle\Entity\Watchlist', 'watchlist');

        if ($criteria->getUser()) {
            $qb->andWhere('watchlist.user = :user')
                ->setParameter('user', $criteria->getUser());
        }

        if ($criteria->getDocumentary()) {
            $qb->andWhere('watchlist.documentary = :documentary')
                ->setParameter('documentary', $criteria->getDocumentary());
        }

        return $qb;
    }
}