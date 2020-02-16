<?php

namespace App\Service;

use App\Criteria\CategoryCriteria;
use App\Entity\Category;
use App\Entity\Documentary;
use App\Enum\CategoryOrderBy;
use App\Enum\CategoryStatus;
use App\Enum\Order;
use App\Repository\CategoryRepository;

class CategoryService
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param string $slug
     * @return Category|null
     */
    public function getCategoryBySlug(string $slug)
    {
        return $this->categoryRepository->findOneBy([
            'slug' => $slug
        ]);
    }

    /**
     * @param int $id
     * @return Category|null
     */
    public function getCategoryById(int $id)
    {
        return $this->categoryRepository->find($id);
    }

    /**
     * @return Category[]|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEnabledCategoriesOrderedByName()
    {
        $categoryCriteria = new CategoryCriteria();
        $categoryCriteria->setStatus(CategoryStatus::ENABLED);
        $categoryCriteria->setGreaterThanEqual(1);
        $categoryCriteria->setSort([
            CategoryOrderBy::NAME => Order::ASC
        ]);

        return $this->categoryRepository->findCategoriesByCriteria($categoryCriteria);
    }

    public function getCategoriesByCriteria(CategoryCriteria $criteria)
    {
        return $this->categoryRepository->findCategoriesByCriteria($criteria);
    }

    /**
     * @return Category[]
     */
    public function getAllCategories()
    {
        return $this->categoryRepository->findAll();
    }

    /**
     * @param Category $newCategory
     * @param Category $oldCategory
     * @param Documentary $documentary
     */
    public function updateDocumentaryCountForCategories(
        Category $newCategory,
        Category $oldCategory,
        Documentary $documentary)
    {
        if ($oldCategory->getId() != $newCategory->getId()) {
            $oldCategory->removeDocumentary($documentary);
            $this->updateDocumentaryCountForCategory($oldCategory);
        }

        $this->updateDocumentaryCountForCategory($newCategory);
    }

    /**
     * @param Category $category
     */
    public function updateDocumentaryCountForCategory(Category $category)
    {
        $count = 0;

        $documentaries = $category->getDocumentaries();
        foreach ($documentaries as $documentary) {
            if ($documentary->isPublished()) {
                $count++;
            }
        }

        $category->setDocumentaryCount($count);
        $this->save($category);
    }

    /**
     * @param Category $category
     * @param bool $sync
     */
    public function save(Category $category, bool $sync = true)
    {
        $this->categoryRepository->save($category, $sync);
    }
}