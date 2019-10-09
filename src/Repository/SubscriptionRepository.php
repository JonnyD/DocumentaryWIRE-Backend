<?php

namespace App\Repository;

use App\Criteria\SubscriptionCriteria;
use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function findSubscriptionByCriteriaQueryBuilder(SubscriptionCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('subscription')
            ->from('App\Entity\Subscription', 'subscription');

        if ($criteria->getFrom()) {
            $qb->andWhere('subscription.userFrom = :from')
                ->setParameter('from', $criteria->getFrom());
        }

        if ($criteria->getTo()) {
            $qb->andWhere('subscription.userTo = :to')
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
     * @param SubscriptionCriteria $criteria
     * @return ArrayCollection|Subscription[]
     */
    public function findSubscriptionsByCriteria(SubscriptionCriteria $criteria)
    {
        $qb = $this->findSubscriptionByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $query->useResultCache(true, 3600, 'my_region')
            ->useQueryCache(true);
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param SubscriptionCriteria $criteria
     * @return Subscription
     */
    public function findSubscriptionByCriteria(SubscriptionCriteria $criteria)
    {
        $criteria->setLimit(1);
        $qb = $this->findSubscriptionByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param Subscription $subscription
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Subscription $subscription, bool $sync = true)
    {
        $this->getEntityManager()->persist($subscription);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param Subscription $subscription
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(Subscription $subscription)
    {
        $this->getEntityManager()->remove($subscription);
        $this->flush();
    }
}
