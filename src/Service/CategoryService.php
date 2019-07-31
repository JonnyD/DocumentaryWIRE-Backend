<?php

namespace App\Service;

use App\Entity\Category;
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
     * @return Category[]
     */
    public function getAllCategories()
    {
        return $this->categoryRepository->findAll();
    }
}