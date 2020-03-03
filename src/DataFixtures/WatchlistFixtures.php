<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Comment;
use App\Entity\Documentary;
use App\Entity\User;
use App\Entity\VideoSource;
use App\Entity\Watchlist;
use App\Enum\ActivityType;
use App\Enum\ComponentType;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use PhpParser\Comment\Doc;

class WatchlistFixtures extends Fixture implements DependentFixtureInterface
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

        $documentary1 = $this->getDocumentary('documentary-1');
        $documentary2 = $this->getDocumentary('documentary-2');
        $documentary3 = $this->getDocumentary('documentary-3');
        $documentary4 = $this->getDocumentary('documentary-4');

        $watchlist1 = $this->createWatchlist($user1, $documentary1);
        $watchlist2 = $this->createWatchlist($user1, $documentary2);
        $watchlist3 = $this->createWatchlist($user2, $documentary1);
        $watchlist4 = $this->createWatchlist($user3, $documentary4);
        $watchlist5 = $this->createWatchlist($user3, $documentary1);
        $watchlist6 = $this->createWatchlist($user4, $documentary3);

        $manager->persist($watchlist1);
        $manager->persist($watchlist2);
        $manager->persist($watchlist3);
        $manager->persist($watchlist4);
        $manager->persist($watchlist5);
        $manager->persist($watchlist6);
        $manager->flush();

        $this->createReference($watchlist1);
        $this->createReference($watchlist2);
        $this->createReference($watchlist3);
        $this->createReference($watchlist4);
        $this->createReference($watchlist5);
        $this->createReference($watchlist6);
    }

    private function createWatchlist(
        User $user,
        Documentary $documentary
    )
    {
        $watchlist = new Watchlist();
        $watchlist->setUser($user);
        $watchlist->setDocumentary($documentary);
        return $watchlist;
    }

    /**
     * @param Watchlist $watchlist
     */
    private function createReference(Watchlist $watchlist)
    {
        $this->addReference('watchlist.'.$watchlist->getUser()->getUsername().'&'.$watchlist->getDocumentary()->getSlug(), $watchlist);
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
            UserFixtures::class
        ];
    }
}