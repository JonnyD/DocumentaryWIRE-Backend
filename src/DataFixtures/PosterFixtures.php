<?php

namespace App\DataFixtures;

use App\Entity\Poster;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Documentary;
use Doctrine\Bundle\FixturesBundle\Fixture;

class PosterFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $documentary1 = $this->getDocumentary('documentary-1');

        $poster1 = $this->createPoster('test.jpg', $documentary1);

        $manager->persist($poster1);
        $manager->flush();
    }

    /**
     * @param string $imagePath
     * @param Documentary $documentary
     * @return Poster
     */
    public function createPoster(string $imagePath, Documentary $documentary)
    {
        $poster = new Poster();
        $poster->setImagePath($imagePath);
        $poster->setDocumentary($documentary);
        return $poster;
    }

    /**
     * @param string $slug
     * @return Documentary
     */
    public function getDocumentary(string $slug)
    {
        return $this->getReference('documentary.'.$slug);
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            DocumentaryFixtures::class
        ];
    }
}