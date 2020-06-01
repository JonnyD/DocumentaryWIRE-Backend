<?php

namespace App\Controller;

use App\Criteria\CategoryCriteria;
use App\Entity\Category;
use App\Enum\CategoryOrderBy;
use App\Enum\CategoryStatus;
use App\Enum\Order;
use App\Form\CategoryForm;
use App\Hydrator\CategoryHydrator;
use App\Service\CategoryService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends BaseController implements ClassResourceInterface
{
    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * @param CategoryService $categoryService
     */
    public function __construct(
        CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @FOSRest\Get("/category", name="get_categories", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $criteria = new CategoryCriteria();

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if ($isRoleAdmin) {
            $status = $request->query->get('status');
            if (isset($status)) {
                $hasStatus = CategoryStatus::hasStatus($status);
                if (!$hasStatus) {
                    return $this->createApiResponse('Status . ' . $status . ' does not exist', 404);
                }

                $criteria->setStatus($status);
            }
        } else {
            $criteria->setStatus(CategoryStatus::ENABLED);
            $criteria->setDocumentaryCountGreaterThanEqual(1);
        }

        $sort = $request->query->get('sort');
        if (isset($sort)) {
            $exploded = explode("-", $sort);

            $hasOrderBy = CategoryOrderBy::hasOrderBy($exploded[0]);
            if (!$hasOrderBy) {
                return $this->createApiResponse('Order by . ' . $exploded[0] . ' does not exist', 404);
            }

            $sort = [$exploded[0] => $exploded[1]];
            $criteria->setSort($sort);
        } else {
            $criteria->setSort([
                CategoryOrderBy::NAME => Order::ASC
            ]);
        }

        $categories = $this->categoryService->getCategoriesByCriteria($criteria);

        $serialized = $this->serializeCategories($categories);
        return $this->createApiResponse($serialized, 200);
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

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if ($category->isDisabled() && !$isRoleAdmin) {
            return $this->createApiResponse("Not authorixed", 403);
        }

        $categoryHydrator = new CategoryHydrator($category);
        $serialized = $categoryHydrator->toArray();

        return $this->createApiResponse($serialized, 200);
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
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        /** @var Category $category */
        $category = $this->categoryService->getCategoryById($id);
        if ($category === null) {
            return $this->createApiResponse("Category not found", 404);
        }

        $form = $this->createForm(CategoryForm::class, $category);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isValid()) {
                $this->categoryService->save($category);

                $categoryHydrator = new CategoryHydrator($category);
                $serializedCategory = $categoryHydrator->toArray();
                return $this->createApiResponse($serializedCategory, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 200);
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
            $categoryHydrator = new CategoryHydrator($category);
            $serializedCategory = $categoryHydrator->toArray();
            $serialized[] = $serializedCategory;
        }

        return $serialized;
    }
}