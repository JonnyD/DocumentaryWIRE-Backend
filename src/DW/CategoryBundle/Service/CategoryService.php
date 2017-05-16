<?php

namespace DW\CategoryBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use DW\CategoryBundle\Entity\Category;
use DW\CategoryBundle\Repository\CategoryRepository;

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
     * @return ArrayCollection|Category[]
     */
    public function getAllCategoriesWithDocumentaries()
    {
        return $this->categoryRepository->findAllWithDocumentaries();
    }

    /**
     * @param string $slug
     * @return Category
     */
    public function getCategoryBySlug(string $slug)
    {
        return $this->categoryRepository->findOneBySlug($slug);
    }
}