<?php

namespace App\Repository;

use App\Criteria\ActivityCriteria;
use App\Entity\Activity;
use App\Enum\Sync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * @param ActivityCriteria $criteria
     * @return QueryBuilder
     */
    public function findAllByCriteriaQueryBuilder(ActivityCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('activity')
            ->from('App\Entity\Activity', 'activity')
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

        if ($criteria->getAuthor()) {
            $qb->andWhere('activity.author = :author')
                ->setParameter('author', $criteria->getAuthor());
        }

        if ($criteria->getEmail()) {
            $qb->andWhere('activity.email = :email')
                ->setParameter('email', $criteria->getEmail());
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
                limit 50) as A";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('sum', 'amount');
        $query = $this->_em->createNativeQuery($sql, $rsm);

        return $query->getSingleScalarResult();
    }

    /**
     * @param Activity $activity
     * @param string $sync
     */
    public function save(Activity $activity, string $sync = Sync::YES)
    {
        $this->getEntityManager()->persist($activity);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }

    /**
     * @param Activity $activity
     * @param string $sync
     */
    public function remove(Activity $activity, string $sync = Sync::YES)
    {
        $this->getEntityManager()->remove($activity);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
