<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryForm;
use App\Service\CategoryService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractFOSRestController implements ClassResourceInterface
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
        $categories = $this->categoryService->getAllCategoriesOrderedByName();

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        $serialized = $this->serializeCategories($categories);

        return new JsonResponse($serialized, 200, $headers);
    }

    /**
     * @FOSRest\Get("/category/{slug}", name="get_category", options={ "method_prefix" = false })
     *
     * @param string $slug
     * @return Category|null
     */
    public function getCategoryAction(string $slug)
    {
        $category = $this->categoryService->getCategoryBySlug($slug);

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Methods: GET, POST',
            'Access-Control-Allow-Credentials: true',
            'Access-Control-Max-Age: 86400',
            'Access-Control-Request-Headers' => [' X-Requested-With'],
        ];

        $serialized = $this->serializeCategory($category);

        return new JsonResponse($serialized, 200, $headers);
    }

    /**
     * @FOSRest\Patch("/category/{id}", name="partial_update_category", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editCategoryAction(int $id, Request $request)
    {
        /** @var Category $category */
        $category = $this->categoryService->getCategoryById($id);

        if ($category === null) {
            return new AccessDeniedException();
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        $form = $this->createForm(CategoryForm::class, $category);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true)['resource'];
            $form->submit($data);

            if ($form->isValid()) {
                $this->categoryService->save($category);
                $serializedCategory = $this->serializeCategory($category);
                return new JsonResponse($serializedCategory, 200, $headers);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return new JsonResponse($errors, 200, $headers);
            }
        }
    }

    /**
     * @param $categories
     * @return array
     */
    private function serializeCategories($categories)
    {
        $serialized = [];

        foreach ($categories as $category) {
            $serializedCategory = $this->serializeCategory($category);
            $serialized[] = $serializedCategory;
        }

        return $serialized;
    }

    /**
     * @param Category $category
     * @return array
     */
    private function serializeCategory(Category $category)
    {
        $serializedCategory = [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug()
        ];

        return $serializedCategory;
    }
}