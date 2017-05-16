<?php

namespace DW\WatchlistBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use DW\WatchlistBundle\Criteria\WatchlistCriteria;
use DW\WatchlistBundle\Entity\Watchlist;

class WatchlistRepository extends EntityRepository
{
    /**
     * @param WatchlistCriteria $criteria
     * @return ArrayCollection|Watchlist[]
     */
    public function findAllByCriteria(WatchlistCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('watchlist')
            ->from('DW\WatchlistBundle\Entity\Watchlist', 'watchlist');

        if ($criteria->getUser()) {
            $qb->andWhere('watchlist.user = :user')
                ->setParameter('user', $criteria->getUser());
        }

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }
}