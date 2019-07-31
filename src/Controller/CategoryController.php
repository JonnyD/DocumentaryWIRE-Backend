<?php

namespace App\Controller;

use App\Service\CategoryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class CategoryController
{
    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * @param CategoryService $categoryService
     * @param Serializer $serializer
     */
    public function __construct(
        CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @FOSRest\Get("/category", name="get_categories", options={ "method_prefix" = false })
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $categories = $this->categoryService->getAllCategories();

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        $serialized = $this->serializeCategories($categories);

        return new JsonResponse($serialized, 200, $headers);
    }

    /**
     * @param $categories
     * @return array
     */
    private function serializeCategories($categories)
    {
        $serialized = [];

        foreach ($categories as $category) {
            $serializedCategory = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug()
            ];

            $serialized[] = $serializedCategory;
        }

        return $serialized;
    }
}