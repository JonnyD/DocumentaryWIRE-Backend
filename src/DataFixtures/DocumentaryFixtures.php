<?php

namespace App\DataFixtures;

use App\Entity\Episodic;
use App\Entity\Standalone;
use App\Entity\User;
use App\Entity\VideoSource;
use App\Enum\DocumentaryType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\DataFixtures\CategoryFixtures;
use App\Entity\Category;
use App\Entity\Documentary;
use App\Enum\DocumentaryStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use PhpParser\Comment\Doc;

class DocumentaryFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user1 = $this->getUser('user1');

        $category1 = $this->getCategory('Category 1');
        $category2 = $this->getCategory('Category 2');
        $category3 = $this->getCategory('Category 3');

        $youtube = $this->getVideoSource('Youtube');
        $vimeo = $this->getVideoSource('Vimeo');

        $standalone1 = $this->createStandalone($vimeo, '234jkjkfs');
        $documentary1 = $this->createStandaloneDocumentary(
            $standalone1,
            $category1, 'Documentary 1', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PENDING, 'poster.jpg',
            true, 10, 90, 2015, $user1);

        $standalone2 = $this->createStandalone($vimeo, '234jkjkfs');
        $documentary2 = $this->createStandaloneDocumentary(
            $standalone2,
            $category2, 'Documentary 2', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH, 'poster.jpg',
            true, 10, 90, 2015, $user1);

        $standalone3 = $this->createStandalone($youtube, '234jkjkfs');
        $documentary3 = $this->createStandaloneDocumentary(
            $standalone3,
            $category3, 'Documentary 3', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,'poster.jpg',
            true, 10, 90, 2015, $user1);

        $standalone4 = $this->createStandalone($youtube, '234jkjkfs');
        $documentary4 = $this->createStandaloneDocumentary(
            $standalone4,
            $category3, 'Documentary 4', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $standalone5 = $this->createStandalone($youtube, '234jkjkfs');
        $documentary5 = $this->createStandaloneDocumentary(
            $standalone5,
            $category3, 'Documentary 5', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $standalone6 = $this->createStandalone($vimeo, '234jkjkfs');
        $documentary6 = $this->createStandaloneDocumentary(
            $standalone6,
            $category3, 'Documentary 6', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $standalone7 = $this->createStandalone($vimeo, '234jkjkfs');
        $documentary7 = $this->createStandaloneDocumentary(
            $standalone7,
            $category3, 'Documentary 7', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $standalone8 = $this->createStandalone($vimeo, '234jkjkfs');
        $documentary8 = $this->createStandaloneDocumentary(
            $standalone8,
            $category3, 'Documentary 8', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $standalone9 = $this->createStandalone($vimeo, '234jkjkfs');
        $documentary9 = $this->createStandaloneDocumentary(
            $standalone9,
            $category3, 'Documentary 9', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $standalone10 = $this->createStandalone($vimeo, '234jkjkfs');
        $documentary10 = $this->createStandaloneDocumentary(
            $standalone10,
            $category3, 'Documentary 10', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $standalone11 = $this->createStandalone($vimeo, '234jkjkfs');
        $documentary11 = $this->createStandaloneDocumentary(
            $standalone11,
            $category3, 'Documentary 11', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $standalone12 = $this->createStandalone($vimeo, '234jkjkfs');
        $documentary12 = $this->createStandaloneDocumentary(
            $standalone12,
            $category3, 'Documentary 12', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $episodic1 = $this->createEpisodic();
        $documentary13 = $this->createEpisodicDocumentary(
            $episodic1,
            $category3, 'Documentary 13', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $episodic2 = $this->createEpisodic();
        $documentary14 = $this->createEpisodicDocumentary(
            $episodic2,
            $category3, 'Documentary 14', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $episodic3 = $this->createEpisodic();
        $documentary15 = $this->createEpisodicDocumentary(
            $episodic3,
            $category3, 'Documentary 15', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $episodic4 = $this->createEpisodic();
        $documentary16 = $this->createEpisodicDocumentary(
            $episodic4,
            $category3, 'Documentary 16', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $episodic5 = $this->createEpisodic();
        $documentary17 = $this->createEpisodicDocumentary(
            $episodic5,
            $category3, 'Documentary 17', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $episodic6 = $this->createEpisodic();
        $documentary18 = $this->createEpisodicDocumentary(
            $episodic6,
            $category3, 'Documentary 18', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $episodic7 = $this->createEpisodic();
        $documentary19 = $this->createEpisodicDocumentary(
            $episodic7,
            $category3, 'Documentary 19', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $episodic8 = $this->createEpisodic();
        $documentary20 = $this->createEpisodicDocumentary(
            $episodic8,
            $category3, 'Documentary 20', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg',
            false, 10, 90, 2015, $user1);

        $manager->persist($documentary1);
        $manager->persist($documentary2);
        $manager->persist($documentary3);
        $manager->persist($documentary4);
        $manager->persist($documentary5);
        $manager->persist($documentary6);
        $manager->persist($documentary7);
        $manager->persist($documentary8);
        $manager->persist($documentary9);
        $manager->persist($documentary10);
        $manager->persist($documentary11);
        $manager->persist($documentary12);
        $manager->persist($documentary13);
        $manager->persist($documentary14);
        $manager->persist($documentary15);
        $manager->persist($documentary16);
        $manager->persist($documentary17);
        $manager->persist($documentary18);
        $manager->persist($documentary19);
        $manager->persist($documentary20);
        $manager->flush();

        $this->createReference($documentary1);
        $this->createReference($documentary2);
        $this->createReference($documentary3);
        $this->createReference($documentary4);
        $this->createReference($documentary5);
        $this->createReference($documentary6);
        $this->createReference($documentary7);
        $this->createReference($documentary8);
        $this->createReference($documentary10);
        $this->createReference($documentary11);
        $this->createReference($documentary12);
        $this->createReference($documentary13);
        $this->createReference($documentary14);
        $this->createReference($documentary15);
        $this->createReference($documentary16);
        $this->createReference($documentary17);
        $this->createReference($documentary18);
        $this->createReference($documentary19);
        $this->createReference($documentary20);
    }

    /**
     * @param Standalone $standalone
     * @param Category $category
     * @param string $title
     * @param string $storyLine
     * @param string $summary
     * @param string $status
     * @param string $poster
     * @param bool $featured
     * @param int $views
     * @param int $length
     * @param int $year
     * @param User $user
     * @return Documentary
     */
    private function createStandaloneDocumentary(
        Standalone $standalone,
        Category $category,
        string $title,
        string $storyLine,
        string $summary,
        string $status,
        string $poster,
        bool $featured,
        int $views,
        int $length,
        int $year,
        User $user
    )
    {
        $documentary = new Documentary();
        $documentary->setStandalone($standalone);
        $documentary->setType(DocumentaryType::STANDALONE);
        $documentary->setCategory($category);
        $documentary->setTitle($title);
        $documentary->setStoryLine($storyLine);
        $documentary->setSummary($summary);
        $documentary->setStatus($status);
        $documentary->setPosterFileName($poster);
        $documentary->setFeatured($featured);
        $documentary->setViews($views);
        $documentary->setLength($length);
        $documentary->setYear($year);
        $documentary->setAddedBy($user);
        return $documentary;
    }

    /**
     * @param Episodic $episodic
     * @param Category $category
     * @param string $title
     * @param string $storyLine
     * @param string $summary
     * @param string $status
     * @param string $poster
     * @param bool $featured
     * @param int $views
     * @param int $length
     * @param int $year
     * @param User $user
     * @return Documentary
     */
    private function createEpisodicDocumentary(
        Episodic $episodic,
        Category $category,
        string $title,
        string $storyLine,
        string $summary,
        string $status,
        string $poster,
        bool $featured,
        int $views,
        int $length,
        int $year,
        User $user
    )
    {
        $documentary = new Documentary();
        $documentary->setEpisodic($episodic);
        $documentary->setType(DocumentaryType::EPISODIC);
        $documentary->setCategory($category);
        $documentary->setTitle($title);
        $documentary->setStoryLine($storyLine);
        $documentary->setSummary($summary);
        $documentary->setStatus($status);
        $documentary->setPosterFileName($poster);
        $documentary->setFeatured($featured);
        $documentary->setViews($views);
        $documentary->setLength($length);
        $documentary->setYear($year);
        $documentary->setAddedBy($user);

        return $documentary;
    }

    /**
     * @param Documentary $documentary
     * @param VideoSource $videoSource
     * @param string $videoId
     * @return Standalone
     */
    private function createStandalone(
        VideoSource $videoSource,
        string $videoId)
    {
        $standalone = new Standalone();
        $standalone->setVideoSource($videoSource);
        $standalone->setVideoId($videoId);

        return $standalone;
    }

    /**
     * @return Episodic
     */
    private function createEpisodic()
    {
        $episodic = new Episodic();

        return $episodic;
    }

    /**
     * @param string $name
     * @return Category
     */
    private function getCategory(string $name)
    {
        return $this->getReference('category.'.$name);
    }

    /**
     * @param string $name
     * @return VideoSource
     */
    private function getVideoSource(string $name)
    {
        return $this->getReference('video-source.'.$name);
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
     * @param Documentary $documentary
     */
    private function createReference(Documentary $documentary)
    {
        $this->addReference('documentary.'.$documentary->getSlug(), $documentary);
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            CategoryFixtures::class,
            VideoSourceFixtures::class,
            UserFixtures::class
        ];
    }
}