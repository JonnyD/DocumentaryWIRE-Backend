<?php

namespace DW\CategoryBundle\Controller\Publc;

use DW\BaseBundle\Enum\Order;
use DW\CategoryBundle\Service\CategoryService;
use DW\DocumentaryBundle\Criteria\DocumentaryCriteria;
use DW\DocumentaryBundle\Enum\DocumentaryStatus;
use DW\DocumentaryBundle\Enum\OrderBy;
use DW\DocumentaryBundle\Service\DocumentaryService;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    /**
     * @param Request $request
     * @param string $slug
     * @return Response
     */
    public function showAction(Request $request, string $slug)
    {
        $categoryService = $this->getCategoryService();
        $category = $categoryService->getCategoryBySlug($slug);

        if ($category == null) {
            throw $this->createNotFoundException();
        }

        $page = $request->query->get('page', 1);

        $criteria = new DocumentaryCriteria();
        $criteria->setStatus(DocumentaryStatus::PUBLISH);
        $criteria->setCategory($category);
        $criteria->setSort([
            OrderBy::CREATED_AT => Order::DESC
        ]);

        $documentaryService = $this->getDocumentaryService();
        $qb = $documentaryService->getDocumentariesByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(12);
        $pagerfanta->setCurrentPage($page);

        return $this->render('CategoryBundle:Publc:show.html.twig', [
            'category' => $category,
            'documentaries' => $pagerfanta
        ]);

    }

    /**
     * @return CategoryService
     */
    private function getCategoryService()
    {
        return $this->get('dw.category_service');
    }

    /**
     * @return DocumentaryService
     */
    private function getDocumentaryService()
    {
        return $this->get('dw.documentary_service');
    }
}