<?php

namespace DW\ActivityBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use DW\ActivityBundle\Criteria\ActivityCriteria;
use DW\ActivityBundle\Entity\Activity;

class ActivityRepository extends EntityRepository
{
    /**
     * @param ActivityCriteria $criteria
     * @return QueryBuilder
     */
    public function findAllByCriteriaQueryBuilder(ActivityCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('activity')
            ->from('DW\ActivityBundle\Entity\Activity', 'activity')
            ->leftJoin('activity.user', 'user');

        if ($criteria->getUser()) {
            $qb->andWhere('activity.user = :user')
                ->setParameter('user', $criteria->getUser());
        }

        if ($criteria->getObjectId()) {
            $qb->andWhere('activity.objectId = :objectId')
                ->setParameter('objectId', $criteria->getObjectId());
        }

        if ($criteria->getType()) {
            $qb->andWhere('activity.type = :type')
                ->setParameter('type', $criteria->getType());
        }

        if ($criteria->getComponent()) {
            $qb->andWhere('activity.component = :component')
                ->setParameter('component', $criteria->getComponent());
        }

        if ($criteria->getSort()) {
            foreach ($criteria->getSort() as $column => $direction) {
                if (strpos($column, '.') === false) {
                    $qb->addOrderBy($qb->getRootAliases()[0] . '.' . $column, $direction);
                } else {
                    $qb->addOrderBy($column, $direction);
                }
            }
        }

        if ($criteria->getLimit()) {
            $qb->setMaxResults($criteria->getLimit());
        }

        return $qb;
    }
    
    /**
     * @param ActivityCriteria $criteria
     * @return ArrayCollection|Activity[]
     */
    public function findAllByCriteria(ActivityCriteria $criteria)
    {
        $qb = $this->findAllByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param ActivityCriteria $criteria
     * @return Activity
     */
    public function findByCriteria(ActivityCriteria $criteria)
    {
        $criteria->setLimit(1);

        $qb = $this->findAllByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @return int
     */
    public function findAmountForRecentWidget()
    {
        $sql = "select sum(count) as sum from (select count(*) as count from activity
                where activity.type = 'like' or activity.type = 'comment' or activity.type = 'joined'
                group by activity.group_number
                order by activity.group_number DESC, activity.created_at DESC
                limit 20) as A";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('sum', 'amount');
        $query = $this->_em->createNativeQuery($sql, $rsm);

        return $query->getSingleScalarResult();
    }

    /**
     * @param Activity $activity
     * @param bool $sync
     */
    public function save(Activity $activity, bool $sync = true)
    {
        $this->getEntityManager()->persist($activity);
        if ($sync) {
            $this->flush();
        }
    }

    /**
     * @param Activity $activity
     * @param bool $sync
     */
    public function remove(Activity $activity, bool $sync = true)
    {
        $this->getEntityManager()->remove($activity);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}