<?php

namespace DW\CategoryBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\CategoryBundle\Entity\Category;
use DW\CategoryBundle\Service\CategoryService;

class CategoriesExtension extends \Twig_Extension
{
    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("categories", [$this, "categories"])
        ];
    }

    /**
     * @return ArrayCollection|Category[]
     */
    public function categories()
    {
        return $this->categoryService->getAllCategoriesWithDocumentaries();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'categories_extension';
    }
}
