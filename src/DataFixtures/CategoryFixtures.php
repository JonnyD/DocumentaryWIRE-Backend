<?php

namespace App\DataFixtures;

use App\Enum\CategoryStatus;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CategoryFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $category1 = $this->createCategory('Category 1', CategoryStatus::ENABLED, 2);
        $category2 = $this->createCategory('Category 2', CategoryStatus::ENABLED, 2);
        $category3 = $this->createCategory('Category 3', CategoryStatus::DISABLED, 0);

        $manager->persist($category1);
        $manager->persist($category2);
        $manager->persist($category3);
        $manager->flush();

        $this->createReference($category1);
        $this->createReference($category2);
        $this->createReference($category3);
    }

    /**
     * @param string $name
     * @param string $status
     * @param int $documentaryCount
     * @return Category
     */
    private function createCategory(string $name, string $status, int $documentaryCount)
    {
        $category = new Category();
        $category->setName($name);
        $category->setStatus($status);
        $category->setDocumentaryCount($documentaryCount);
        return $category;
    }

    /**
     * @param Category $category
     */
    private function createReference(Category $category)
    {
        $this->addReference('category.'.$category->getName(), $category);
    }
}