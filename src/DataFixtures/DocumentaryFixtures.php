<?php

namespace App\DataFixtures;

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

        $documentary1 = $this->createDocumentary(
            $category1, 'Documentary 1', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH, 'poster.jpg', '234jkjkfs', 'youtube',
            true, 10, 90, 2015);
        $documentary2 = $this->createDocumentary(
            $category2, 'Documentary 2', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH, 'poster.jpg', '234jkjkfs', 'youtube',
            true, 10, 90, 2015);
        $documentary3 = $this->createDocumentary(
            $category3, 'Documentary 3', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,'poster.jpg', '234jkjkfs', 'youtube',
            true, 10, 90, 2015);
        $documentary4 = $this->createDocumentary(
            $category3, 'Documentary 4', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', 'youtube',
            false, 10, 90, 2015);
        $documentary5 = $this->createDocumentary(
            $category3, 'Documentary 5', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', 'youtube',
            false, 10, 90, 2015);
        $documentary6 = $this->createDocumentary(
            $category3, 'Documentary 6', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', 'youtube',
            false, 10, 90, 2015);
        $documentary7 = $this->createDocumentary(
            $category3, 'Documentary 7', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', 'youtube',
            false, 10, 90, 2015);
        $documentary8 = $this->createDocumentary(
            $category3, 'Documentary 8', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', 'youtube',
            false, 10, 90, 2015);
        $documentary9 = $this->createDocumentary(
            $category3, 'Documentary 9', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISH,  'poster.jpg', '234jkjkfs', 'youtube',
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
        $manager->flush();

        $this->createReference($documentary1);
        $this->createReference($documentary2);
        $this->createReference($documentary3);
        $this->createReference($documentary4);
        $this->createReference($documentary5);
        $this->createReference($documentary6);
        $this->createReference($documentary7);
        $this->createReference($documentary8);
        $this->createReference($documentary9);
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
        string $videoSource,
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
        $documentary->setPoster($poster);
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
     * @param Documentary $documentary
     */
    private function createReference(Documentary $documentary)
    {
        $this->addReference('documentary.'.$documentary->getTitle(), $documentary);
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            CategoryFixtures::class
        ];
    }
}