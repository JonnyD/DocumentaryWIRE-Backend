<?php

namespace App\Controller;

use App\Criteria\WatchlistCriteria;
use App\Entity\Documentary;
use App\Entity\Watchlist;
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

class WatchlistController extends AbstractFOSRestController implements ClassResourceInterface
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
        $page = $request->query->get('page', 1);

        $criteria = new WatchlistCriteria();

        $username = $request->query->get('user');
        if (isset($username)) {
            $user = $this->userService->getUserByUsername($username);
            $criteria->setUser($user);
        }

        $documentarySlug = $request->query->get('documentary');
        if (isset($documentary)) {
            $documentary = $this->documentaryService->getDocumentaryBySlug($documentarySlug);
            $criteria->setDocumentary($documentary);
        }

        $sort = $request->query->get('sort');
        if (isset($sort)) {
            $exploded = explode("-", $sort);
            $sort = [$exploded[0] => $exploded[1]];
            $criteria->setSort($sort);
        } else {
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
            $serialized[] = $this->serializeWatchlist($item);
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

    private function serializeWatchlist(Watchlist $watchlist)
    {
        $user = $watchlist->getUser();
        $documentary = $watchlist->getDocumentary();

        return [
            'user' => [
                'username' => $user->getUsername(),
                'name' => $user->getName()
            ],
            'documentary' => [
                'title' => $documentary->getTitle(),
                'slug' => $documentary->getSlug(),
                'poster' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPosterFileName(),
                'summary' => $documentary->getSummary()
            ]
        ];
    }
}