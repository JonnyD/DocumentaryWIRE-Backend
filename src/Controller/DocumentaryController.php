<?php

namespace App\Controller;

use App\Entity\Documentary;
use App\Entity\User;
use App\Enum\DocumentaryOrderBy;
use App\Enum\DocumentaryStatus;
use App\Enum\Order;
use App\Service\DocumentaryService;
use App\Criteria\DocumentaryCriteria;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DocumentaryController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param DocumentaryService $documentaryService
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        DocumentaryService $documentaryService,
        TokenStorageInterface $tokenStorage)
    {
        $this->documentaryService = $documentaryService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @FOSRest\Get("/documentary", name="post_documentary_admin_list", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function adminListAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse("Not granted");
        }

        $page = $request->query->get('page', 1);

        $criteria = new DocumentaryCriteria();
        $criteria->setStatus(DocumentaryStatus::PUBLISH);
        $criteria->setSort([
            DocumentaryOrderBy::CREATED_AT => Order::DESC
        ]);

        $qb = $this->documentaryService->getDocumentariesByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(12);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $data = [
            'items'             => $items,
            'count_results'     => $pagerfanta->getNbResults(),
            'current_page'      => $pagerfanta->getCurrentPage(),
            'number_of_pages'   => $pagerfanta->getNbPages(),
            'next'              => ($pagerfanta->hasNextPage()) ? $pagerfanta->getNextPage() : null,
            'prev'              => ($pagerfanta->hasPreviousPage()) ? $pagerfanta->getPreviousPage() : null,
            'paginate'          => $pagerfanta->haveToPaginate(),
        ];

        return new JsonResponse($data);
    }
}