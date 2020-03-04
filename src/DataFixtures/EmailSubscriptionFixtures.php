<?php

namespace App\DataFixtures;

use App\Entity\Email;
use App\Entity\User;
use App\Enum\CategoryStatus;
use App\Enum\YesNo;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;

class EmailSubscriptionFixtures extends Fixture implements DependentFixtureInterface
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

        $isSubscribed = YesNo::YES;
        $subscriptionKey = '1';
        $emailAddress = $user1->getEmail();
        $email1 = $this->createEmailSubscription(
            $isSubscribed, $subscriptionKey, $emailAddress);

        $isSubscribed = YesNo::YES;
        $subscriptionKey = '2';
        $emailAddress = $user2->getEmail();
        $email2 = $this->createEmailSubscription(
            $isSubscribed, $subscriptionKey, $emailAddress);

        $isSubscribed = YesNo::NO;
        $subscriptionKey = '3';
        $emailAddress = $user3->getEmail();
        $email3 = $this->createEmailSubscription(
            $isSubscribed, $subscriptionKey, $emailAddress);

        $isSubscribed = YesNo::NO;
        $subscriptionKey = '4';
        $emailAddress = $user4->getEmail();
        $email4 = $this->createEmailSubscription(
            $isSubscribed, $subscriptionKey, $emailAddress);

        $isSubscribed = YesNo::YES;
        $subscriptionKey = '5';
        $emailAddress = '1xxx@xxx.com';
        $email5 = $this->createEmailSubscription(
            $isSubscribed, $subscriptionKey, $emailAddress);

        $isSubscribed = YesNo::YES;
        $subscriptionKey = '6';
        $emailAddress = '2xxx@xxx.com';
        $email6 = $this->createEmailSubscription(
            $isSubscribed, $subscriptionKey, $emailAddress);

        $manager->persist($email1);
        $manager->persist($email2);
        $manager->persist($email3);
        $manager->persist($email4);
        $manager->persist($email5);
        $manager->persist($email6);
        $manager->flush();

        $this->createReference($email1);
        $this->createReference($email2);
        $this->createReference($email3);
        $this->createReference($email4);
        $this->createReference($email5);
        $this->createReference($email6);

    }

    /**
     * @param string $isSubscribed
     * @param string $subscriptionKey
     * @param string $emailAddress
     * @return Email
     */
    private function createEmailSubscription(
        string $isSubscribed,
        string $subscriptionKey,
        string $emailAddress
    )
    {
        $email = new Email();
        $email->setSubscribed($isSubscribed);
        $email->setSubscriptionKey($subscriptionKey);
        $email->setEmail($emailAddress);
        return $email;
    }

    /**
     * @param Email $email
     */
    private function createReference(Email $email)
    {
        $this->addReference('email.'.$email->getEmail(), $email);
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