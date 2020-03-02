<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Comment;
use App\Entity\Documentary;
use App\Entity\User;
use App\Entity\VideoSource;
use App\Enum\ActivityType;
use App\Enum\ComponentType;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ActivityFixtures extends Fixture implements DependentFixtureInterface
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
        $user5 = $this->getUser('user5');
        $user6 = $this->getUser('user6');

        $comment1 = $this->getComment('This is a comment 1');

        $documentary1 = $this->getDocumentary('documentary-1');
        $documentary2 = $this->getDocumentary('documentary-2');
        $documentary3 = $this->getDocumentary('documentary-3');

        $activity1 = $this->createActivity(ActivityType::WATCHLIST, $user1, ComponentType::DOCUMENTARY, $documentary1->getId(), 5);
        $activity2 = $this->createActivity(ActivityType::WATCHLIST, $user1, ComponentType::DOCUMENTARY, $documentary2->getId(), 5);
        $activity3 = $this->createActivity(ActivityType::WATCHLIST, $user1, ComponentType::DOCUMENTARY, $documentary3->getId(), 5);

        $activity4 = $this->createActivity(ActivityType::JOINED, $user1, ComponentType::USER, $user1->getId(), 4);
        $activity5 = $this->createActivity(ActivityType::JOINED, $user2, ComponentType::USER, $user2->getId(), 4);
        $activity6 = $this->createActivity(ActivityType::JOINED, $user3, ComponentType::USER, $user3->getId(), 4);

        $activity7 = $this->createActivity(ActivityType::WATCHLIST, $user4, ComponentType::DOCUMENTARY, $documentary1->getId(), 3);
        $activity8 = $this->createActivity(ActivityType::WATCHLIST, $user4, ComponentType::DOCUMENTARY, $documentary2->getId(), 3);
        $activity9 = $this->createActivity(ActivityType::WATCHLIST, $user4, ComponentType::DOCUMENTARY, $documentary3->getId(), 3);

        $activity10 = $this->createActivity(ActivityType::COMMENT, $user5, ComponentType::DOCUMENTARY, $comment1->getId(), 2);

        $activity11 = $this->createActivity(ActivityType::JOINED, $user4, ComponentType::USER, $user4->getId(), 1);
        $activity12 = $this->createActivity(ActivityType::JOINED, $user5, ComponentType::USER, $user5->getId(), 1);
        $activity13 = $this->createActivity(ActivityType::JOINED, $user6, ComponentType::USER, $user6->getId(), 1);

        $manager->persist($activity1);
        $manager->persist($activity2);
        $manager->persist($activity3);
        $manager->persist($activity4);
        $manager->persist($activity5);
        $manager->persist($activity6);
        $manager->persist($activity7);
        $manager->persist($activity8);
        $manager->persist($activity9);
        $manager->persist($activity10);
        $manager->persist($activity11);
        $manager->persist($activity12);
        $manager->persist($activity13);
        $manager->flush();

        $activity1->setCreatedAt(new \DateTime('2020-01-20'));
        $activity2->setCreatedAt(new \DateTime('2020-01-19'));
        $activity3->setCreatedAt(new \DateTime('2020-01-18'));
        $activity4->setCreatedAt(new \DateTime('2020-01-17'));
        $activity5->setCreatedAt(new \DateTime('2020-01-16'));
        $activity6->setCreatedAt(new \DateTime('2020-01-15'));
        $activity7->setCreatedAt(new \DateTime('2020-01-14'));
        $activity8->setCreatedAt(new \DateTime('2020-01-13'));
        $activity9->setCreatedAt(new \DateTime('2020-01-12'));
        $activity10->setCreatedAt(new \DateTime('2020-01-11'));
        $activity11->setCreatedAt(new \DateTime('2020-01-10'));
        $activity12->setCreatedAt(new \DateTime('2020-01-09'));
        $activity13->setCreatedAt(new \DateTime('2020-01-08'));

        $manager->persist($activity1);
        $manager->persist($activity2);
        $manager->persist($activity3);
        $manager->persist($activity4);
        $manager->persist($activity5);
        $manager->persist($activity6);
        $manager->persist($activity7);
        $manager->persist($activity8);
        $manager->persist($activity9);
        $manager->persist($activity10);
        $manager->persist($activity11);
        $manager->persist($activity12);
        $manager->persist($activity13);
        $manager->flush();
    }

    private function createActivity(
        string $type,
        User $user,
        string $component,
        int $objectId,
        int $groupNumber
    )
    {
        $activity = new Activity();
        $activity->setType($type);
        $activity->setUser($user);
        $activity->setComponent($component);
        $activity->setObjectId($objectId);
        $activity->setGroupNumber($groupNumber);
        return $activity;
    }

    /**
     * @param VideoSource $videoSource
     */
    private function createReference(VideoSource $videoSource)
    {
        $this->addReference('video-source.'.$videoSource->getName(), $videoSource);
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
     * @param string $commentText
     * @return Comment
     */
    private function getComment(string $commentText)
    {
        return $this->getReference('comment.'.$commentText);
    }

    /**
     * @param string $slug
     * @return Documentary
     */
    private function getDocumentary(string $slug)
    {
        return $this->getReference('documentary.'.$slug);
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            DocumentaryFixtures::class,
            UserFixtures::class,
            CommentFixtures::class
        ];
    }
}