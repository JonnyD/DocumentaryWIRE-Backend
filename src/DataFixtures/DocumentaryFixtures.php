<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use App\Entity\Series;
use App\Entity\User;
use App\Entity\VideoSource;
use App\Enum\DocumentaryType;
use App\Enum\Featured;
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

        /**
         * Create Movies
         */
        $movie1 = $this->createMovie($vimeo, '234jkjkfs');
        $documentary1 = $this->createMovieDocumentary(
            $movie1,
            $category1, 'Documentary 1', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PENDING, 'poster.jpg',
            Featured::YES, 10, 90, 2015, $user1);

        $movie2 = $this->createMovie($vimeo, '234jkjkfs');
        $documentary2 = $this->createMovieDocumentary(
            $movie2,
            $category2, 'Documentary 2', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED, 'poster.jpg',
            Featured::YES, 10, 90, 2011, $user1);

        $movie3 = $this->createMovie($youtube, '234jkjkfs');
        $documentary3 = $this->createMovieDocumentary(
            $movie3,
            $category3, 'Documentary 3', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,'poster.jpg',
            Featured::YES, 10, 90, 2010, $user1);

        $movie4 = $this->createMovie($youtube, '234jkjkfs');
        $documentary4 = $this->createMovieDocumentary(
            $movie4,
            $category3, 'Documentary 4', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2015, $user1);

        $movie5 = $this->createMovie($youtube, '234jkjkfs');
        $documentary5 = $this->createMovieDocumentary(
            $movie5,
            $category3, 'Documentary 5', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2016, $user1);

        $movie6 = $this->createMovie($vimeo, '234jkjkfs');
        $documentary6 = $this->createMovieDocumentary(
            $movie6,
            $category3, 'Documentary 6', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2009, $user1);

        $movie7 = $this->createMovie($vimeo, '234jkjkfs');
        $documentary7 = $this->createMovieDocumentary(
            $movie7,
            $category3, 'Documentary 7', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2001, $user1);

        $movie8 = $this->createMovie($vimeo, '234jkjkfs');
        $documentary8 = $this->createMovieDocumentary(
            $movie8,
            $category3, 'Documentary 8', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2017, $user1);

        $movie9 = $this->createMovie($vimeo, '234jkjkfs');
        $documentary9 = $this->createMovieDocumentary(
            $movie9,
            $category3, 'Documentary 9', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2016, $user1);

        $movie10 = $this->createMovie($vimeo, '234jkjkfs');
        $documentary10 = $this->createMovieDocumentary(
            $movie10,
            $category3, 'Documentary 10', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2018, $user1);

        $movie11 = $this->createMovie($vimeo, '234jkjkfs');
        $documentary11 = $this->createMovieDocumentary(
            $movie11,
            $category3, 'Documentary 11', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2010, $user1);

        $movie12 = $this->createMovie($vimeo, '234jkjkfs');
        $documentary12 = $this->createMovieDocumentary(
            $movie12,
            $category3, 'Documentary 12', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2011, $user1);

        /**
         * Create Series
         */
        $series1 = $this->createSeries();
        $documentary13 = $this->createSeriesDocumentary(
            $series1,
            $category3, 'Documentary 13', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2012, $user1);

        $series2 = $this->createSeries();
        $documentary14 = $this->createSeriesDocumentary(
            $series2,
            $category3, 'Documentary 14', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2013, $user1);

        $series3 = $this->createSeries();
        $documentary15 = $this->createSeriesDocumentary(
            $series3,
            $category3, 'Documentary 15', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2002, $user1);

        $series4 = $this->createSeries();
        $documentary16 = $this->createSeriesDocumentary(
            $series4,
            $category3, 'Documentary 16', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2003, $user1);

        $series5 = $this->createSeries();
        $documentary17 = $this->createSeriesDocumentary(
            $series5,
            $category3, 'Documentary 17', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2000, $user1);

        $series6 = $this->createSeries();
        $documentary18 = $this->createSeriesDocumentary(
            $series6,
            $category3, 'Documentary 18', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2015, $user1);

        $series7 = $this->createSeries();
        $documentary19 = $this->createSeriesDocumentary(
            $series7,
            $category3, 'Documentary 19', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2017, $user1);

        $series8 = $this->createSeries();
        $documentary20 = $this->createSeriesDocumentary(
            $series8,
            $category3, 'Documentary 20', 'This is a storyline', 'Storyline',
            DocumentaryStatus::PUBLISHED,  'poster.jpg',
            Featured::NO, 10, 90, 2019, $user1);

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
     * @param Movie $movie
     * @param Category $category
     * @param string $title
     * @param string $storyLine
     * @param string $summary
     * @param string $status
     * @param string $poster
     * @param string $featured
     * @param int $views
     * @param int $length
     * @param int $year
     * @param User $user
     * @return Documentary
     */
    private function createMovieDocumentary(
        Movie $movie,
        Category $category,
        string $title,
        string $storyLine,
        string $summary,
        string $status,
        string $poster,
        string $featured,
        int $views,
        int $length,
        int $year,
        User $user
    )
    {
        $documentary = new Documentary();
        $documentary->setMovie($movie);
        $documentary->setType(DocumentaryType::MOVIE);
        $documentary->setCategory($category);
        $documentary->setTitle($title);
        $documentary->setStoryLine($storyLine);
        $documentary->setSummary($summary);
        $documentary->setStatus($status);
        $documentary->setPoster($poster);
        $documentary->setFeatured($featured);
        $documentary->setViews($views);
        $documentary->setLength($length);
        $documentary->setYearFrom($year);
        $documentary->setAddedBy($user);
        return $documentary;
    }

    /**
     * @param Series $series
     * @param Category $category
     * @param string $title
     * @param string $storyLine
     * @param string $summary
     * @param string $status
     * @param string $poster
     * @param bool $featured
     * @param int $views
     * @param int $length
     * @param int $yearFrom
     * @param User $user
     * @return Documentary
     */
    private function createSeriesDocumentary(
        Series $series,
        Category $category,
        string $title,
        string $storyLine,
        string $summary,
        string $status,
        string $poster,
        string $featured,
        int $views,
        int $length,
        int $yearFrom,
        User $user
    )
    {
        $documentary = new Documentary();
        $documentary->setSeries($series);
        $documentary->setType(DocumentaryType::SERIES);
        $documentary->setCategory($category);
        $documentary->setTitle($title);
        $documentary->setStoryLine($storyLine);
        $documentary->setSummary($summary);
        $documentary->setStatus($status);
        $documentary->setPoster($poster);
        $documentary->setFeatured($featured);
        $documentary->setViews($views);
        $documentary->setLength($length);
        $documentary->setYearFrom($yearFrom);
        $documentary->setAddedBy($user);

        return $documentary;
    }

    /**
     * @param Documentary $documentary
     * @param VideoSource $videoSource
     * @param string $videoId
     * @return Movie
     */
    private function createMovie(
        VideoSource $videoSource,
        string $videoId)
    {
        $movie = new Movie();
        $movie->setVideoSource($videoSource);
        $movie->setVideoId($videoId);

        return $movie;
    }

    /**
     * @return Series
     */
    private function createSeries()
    {
        $episodic = new Series();

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