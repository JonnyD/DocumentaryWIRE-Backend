<?php

namespace App\Hydrator;

use App\Entity\Subscription;

class SubscriptionHydrator implements HydratorInterface
{
    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @param Subscription $subscription
     */
    public function __construct(
        Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function toArray()
    {
        return [
            'id' => $this->subscription->getId(),
            'userFrom' => [
                'id' => $this->subscription->getUserFrom()->getId(),
                'username' => $this->subscription->getUserFrom()->getUsername()
            ],
            'userTo' => [
                'id' => $this->subscription->getUserTo()->getId(),
                'username' => $this->subscription->getUserTo()->getUsername()
            ],
            'createdAt' => $this->subscription->getCreatedAt(),
            'updatedAt' => $this->subscription->getUpdatedAt()
        ];
    }
}