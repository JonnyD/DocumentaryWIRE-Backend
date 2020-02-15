<?php

namespace App\Service;

use App\Criteria\CategoryCriteria;
use App\Entity\Category;
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
     * @param Category $category
     * @param bool $sync
     */
    public function save(Category $category, bool $sync = true)
    {
        $this->categoryRepository->save($category, $sync);
    }
}