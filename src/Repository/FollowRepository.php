<?php

namespace App\Repository;

use App\Criteria\FollowCriteria;
use App\Entity\Follow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class FollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Follow::class);
    }

    public function findFollowByCriteriaQueryBuilder(FollowCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('follow')
            ->from('App\Entity\Follow', 'follow');

        if ($criteria->getFrom()) {
            $qb->andWhere('follow.userFrom = :from')
                ->setParameter('from', $criteria->getFrom());
        }

        if ($criteria->getTo()) {
            $qb->andWhere('follow.userTo = :to')
                ->setParameter('to', $criteria->getTo());
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
     * @param FollowCriteria $criteria
     * @return ArrayCollection|Follow[]
     */
    public function findSubscriptionsByCriteria(FollowCriteria $criteria)
    {
        $qb = $this->findFollowByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $query->useResultCache(true, 3600, 'my_region')
            ->useQueryCache(true);
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param FollowCriteria $criteria
     * @return Follow
     */
    public function findFollowByCriteria(FollowCriteria $criteria)
    {
        $criteria->setLimit(1);
        $qb = $this->findFollowByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param Follow $follow
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Follow $follow, bool $sync = true)
    {
        $this->getEntityManager()->persist($follow);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param Follow $follow
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(Follow $follow)
    {
        $this->getEntityManager()->remove($follow);
        $this->flush();
    }
}
