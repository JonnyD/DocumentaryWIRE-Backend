<?php

namespace App\DataFixtures;

use App\Entity\VideoSource;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\DataFixtures\CategoryFixtures;
use App\Entity\Category;
use App\Entity\Documentary;
use App\Enum\DocumentaryStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;

class DocumentaryFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $category1 = $this->getCategory('Category 1');
        $category2 = $this->getCategory('Category 2');
        $category3 = $this->getCategory('Category 3');

        $youtube = $this->getVideoSource('Youtube');

        $documentary1 = $this->createDocumentary(
            $category1, 'Documentary 1', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH, 'poster.jpg', '234jkjkfs', $youtube,
            true, 10, 90, 2015);
        $documentary2 = $this->createDocumentary(
            $category2, 'Documentary 2', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH, 'poster.jpg', '234jkjkfs', $youtube,
            true, 10, 90, 2015);
        $documentary3 = $this->createDocumentary(
            $category3, 'Documentary 3', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,'poster.jpg', '234jkjkfs', $youtube,
            true, 10, 90, 2015);
        $documentary4 = $this->createDocumentary(
            $category3, 'Documentary 4', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary5 = $this->createDocumentary(
            $category3, 'Documentary 5', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary6 = $this->createDocumentary(
            $category3, 'Documentary 6', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary7 = $this->createDocumentary(
            $category3, 'Documentary 7', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary8 = $this->createDocumentary(
            $category3, 'Documentary 8', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary9 = $this->createDocumentary(
            $category3, 'Documentary 9', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary10 = $this->createDocumentary(
            $category3, 'Documentary 10', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary11 = $this->createDocumentary(
            $category3, 'Documentary 11', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary12 = $this->createDocumentary(
            $category3, 'Documentary 12', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary13 = $this->createDocumentary(
            $category3, 'Documentary 13', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary14 = $this->createDocumentary(
            $category3, 'Documentary 14', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary15 = $this->createDocumentary(
            $category3, 'Documentary 15', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary16 = $this->createDocumentary(
            $category3, 'Documentary 16', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary17 = $this->createDocumentary(
            $category3, 'Documentary 17', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary18 = $this->createDocumentary(
            $category3, 'Documentary 18', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary19 = $this->createDocumentary(
            $category3, 'Documentary 19', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);
        $documentary20 = $this->createDocumentary(
            $category3, 'Documentary 20', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', $youtube,
            false, 10, 90, 2015);

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
     * @param Category $category
     * @param string $title
     * @param string $storyLine
     * @param string $summary
     * @param string $status
     * @param string $poster
     * @param string $videoId
     * @param string $videoSource
     * @param bool $featured
     * @param int $views
     * @param int $length
     * @param int $year
     * @return Documentary
     */
    private function createDocumentary(
        Category $category,
        string $title,
        string $storyLine,
        string $summary,
        string $status,
        string $poster,
        string $videoId,
        VideoSource $videoSource,
        bool $featured,
        int $views,
        int $length,
        int $year
    )
    {
        $documentary = new Documentary();
        $documentary->setCategory($category);
        $documentary->setTitle($title);
        $documentary->setStoryLine($storyLine);
        $documentary->setSummary($summary);
        $documentary->setStatus($status);
        $documentary->setVideoId($videoId);
        $documentary->setVideoSource($videoSource);
        $documentary->setFeatured($featured);
        $documentary->setViews($views);
        $documentary->setLength($length);
        $documentary->setYear($year);
        return $documentary;
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
            VideoSourceFixtures::class
        ];
    }
}