<?php

namespace App\Service;

use App\Criteria\SubscriptionCriteria;
use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;

class SubscriptionService
{
    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function __construct(
        SubscriptionRepository $subscriptionRepository
    )
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @return Subscription[]
     */
    public function getAllSubscriptions()
    {
        return $this->subscriptionRepository->findAll();
    }

    /**
     * @param int $id
     * @return null|Subscription
     */
    public function getSubscriptionById(int $id)
    {
        return $this->subscriptionRepository->find($id);
    }

    /**
     * @param SubscriptionCriteria $criteria
     * @return Subscription
     */
    public function getSubscriptionByCriteria(SubscriptionCriteria $criteria)
    {
        return $this->subscriptionRepository->findSubscriptionByCriteria($criteria);
    }

    /**
     * @param SubscriptionCriteria $criteria
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSubscriptionsByCriteriaQueryBuilder(SubscriptionCriteria $criteria)
    {
        return $this->subscriptionRepository->findSubscriptionByCriteriaQueryBuilder($criteria);
    }

    /**
     * @param Subscription $subscription
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Subscription $subscription, bool $sync = true)
    {
        $this->subscriptionRepository->save($subscription, $sync);
    }

    /**
     * @param Subscription $subscription
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(Subscription $subscription)
    {
        $this->subscriptionRepository->remove($subscription);
    }
}