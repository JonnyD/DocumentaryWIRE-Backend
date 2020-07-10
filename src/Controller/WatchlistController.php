<?php

namespace App\Controller;

use App\Criteria\WatchlistCriteria;
use App\Entity\Documentary;
use App\Entity\Watchlist;
use App\Enum\WatchlistOrderBy;
use App\Form\WatchlistForm;
use App\Hydrator\WatchlistHydrator;
use App\Service\DocumentaryService;
use App\Service\UserService;
use App\Service\WatchlistService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class WatchlistController extends BaseController implements ClassResourceInterface
{
    /**
     * @var WatchlistService
     */
    private $watchlistService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param WatchlistService $watchlistService
     * @param UserService $userService
     * @param DocumentaryService $documentaryService
     * @param RequestStack $requestStack
     */
    public function __construct(
        WatchlistService $watchlistService,
        UserService $userService,
        DocumentaryService $documentaryService,
        RequestStack $requestStack)
    {
        $this->watchlistService = $watchlistService;
        $this->userService = $userService;
        $this->documentaryService = $documentaryService;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @FOSRest\Get("/watchlist", name="get_watchlist_list", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function listAction(Request $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->createApiResponse('Not authorized', 401);
        }

        $page = $request->query->get('page', 1);

        $criteria = new WatchlistCriteria();

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if ($isRoleAdmin) {
            $username = $request->query->get('user');
            if (isset($username)) {
                $user = $this->userService->getUserByUsername($username);
                $criteria->setUser($user);
            }
        } else {
            $user = $this->getLoggedInUser();
            $criteria->setUser($user);
        }

        $documentaryId = $request->query->get('documentary');
        if (isset($documentaryId)) {
            $documentary = $this->documentaryService->getDocumentaryById($documentaryId);
            $criteria->setDocumentary($documentary);
        }

        $sort = $request->query->get('sort');
        if (isset($sort)) {
            $exploded = explode("-", $sort);
            $sort = [$exploded[0] => $exploded[1]];

            $hasOrderBy = WatchlistOrderBy::hasOrderBy($exploded[0]);
            if (!$hasOrderBy) {
                return $this->createApiResponse('Order by ' . $exploded[0] . ' does not exist', 404);
            }
            $criteria->setSort($sort);
        } else {
            //@TODO
        }

        $amountPerPage = $request->query->get('amountPerPage', 12);
        if (isset($amountPerPage) && $amountPerPage > 50) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $qb = $this->watchlistService->getWatchlistByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($amountPerPage);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $serialized = [];
        foreach ($items as $item) {
            $watchlistHydrator = new WatchlistHydrator($item, $this->request);
            $serialized[] = $watchlistHydrator->toArray();
        }

        $data = [
            'items'             => $serialized,
            'count_results'     => $pagerfanta->getNbResults(),
            'current_page'      => $pagerfanta->getCurrentPage(),
            'number_of_pages'   => $pagerfanta->getNbPages(),
            'next'              => ($pagerfanta->hasNextPage()) ? $pagerfanta->getNextPage() : null,
            'prev'              => ($pagerfanta->hasPreviousPage()) ? $pagerfanta->getPreviousPage() : null,
            'paginate'          => $pagerfanta->haveToPaginate(),
        ];

        return $this->createApiResponse($data, 200);
    }

    /**
     * @FOSRest\Get("/watchlist/{id}", name="get_watchlist", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getWatchlistAction(int $id)
    {
        $watchlist = $this->watchlistService->getWatchlistById($id);
        if ($watchlist === null) {
            return $this->createApiResponse('Watchlist not found', 404);
        }

        $watchlistHydrator = new WatchlistHydrator($watchlist, $this->request);
        $data = $watchlistHydrator->toArray();
        $response = $this->createApiResponse($data, 200);

        return $response;
    }

    public function createWatchlistAction(Request $request)
    {
        $watchlist = new Watchlist();

        $form = $this->createForm(WatchlistForm::class, $watchlist);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isValid()) {
                $this->watchlistService->save($watchlist);

                $watchlistHydrator = new WatchlistHydrator($watchlist, $this->request);
                $serializedCategory = $watchlistHydrator->toArray();
                return $this->createApiResponse($serializedCategory, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 200);
            }
        }

    }
}