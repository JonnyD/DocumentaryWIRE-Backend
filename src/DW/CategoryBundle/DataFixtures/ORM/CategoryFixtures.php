<?php

namespace DW\CategoryBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use DW\CategoryBundle\Entity\Category;

class CategoryFixtures extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $category1 = $this->createCategory('Category 1', 1);
        $category2 = $this->createCategory('Category 2', 1);
        $category3 = $this->createCategory('Category 3', 1);

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
     * @param int $count
     * @return Category
     */
    private function createCategory(string $name, int $count)
    {
        $category = new Category();
        $category->setName($name);
        $category->setCount($count);
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