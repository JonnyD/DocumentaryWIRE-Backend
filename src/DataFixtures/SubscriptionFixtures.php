<?php

namespace App\DataFixtures;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class SubscriptionFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user1 = $this->getUser('user1');
        $user2 = $this->getUser('user2');
        $user3 = $this->getUser('user3');
        $user4 = $this->getUser('user4');

        $subscription1 = $this->createSubscription($user1, $user2);
        $subscription2 = $this->createSubscription($user1, $user3);
        $subscription3 = $this->createSubscription($user2, $user1);
        $subscription4 = $this->createSubscription($user3, $user4);

        $manager->persist($subscription1);
        $manager->persist($subscription2);
        $manager->persist($subscription3);
        $manager->persist($subscription4);
        $manager->flush();

        $this->createReference($subscription1);
        $this->createReference($subscription2);
        $this->createReference($subscription3);
        $this->createReference($subscription4);
    }

    /**
     * @param User $userFrom
     * @param User $userTo
     * @return Subscription
     */
    private function createSubscription(
        User $userFrom,
        User $userTo
    )
    {
        $subscription = new Subscription();
        $subscription->setUserFrom($userFrom);
        $subscription->setUserTo($userTo);
        return $subscription;
    }

    /**
     * @param Subscription $subscription
     */
    private function createReference(Subscription $subscription)
    {
        $this->addReference('subscription.'.$subscription->getUserFrom()->getUsername().'&'.$subscription->getUserTo()->getUsername(), $subscription);
    }

    /**
     * @param string $username
     * @return User
     */
    private function getUser(string $username)
    {
        return $this->getReference('user.'.$username);
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}