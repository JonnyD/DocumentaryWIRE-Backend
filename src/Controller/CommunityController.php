<?php

namespace App\Controller;

use App\Criteria\ActivityCriteria;
use App\Entity\Activity;
use App\Enum\ActivityOrderBy;
use App\Enum\ActivityType;
use App\Enum\Order;
use App\Service\ActivityService;
use App\Service\DocumentaryService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CommunityController extends BaseController implements ClassResourceInterface
{
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var null|Request
     */
    private $request;

    /**
     * @param ActivityService $activityService
     * @param DocumentaryService $documentaryService
     * @param RequestStack $requestStack
     */
    public function __construct(
        ActivityService $activityService,
        DocumentaryService $documentaryService,
        RequestStack $requestStack)
    {
        $this->activityService = $activityService;
        $this->documentaryService = $documentaryService;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @FOSRest\Get("/community", name="get_community", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        $criteria = new ActivityCriteria();

        $sort = $request->query->get('sort');
        if (isset($sort)) {
            $exploded = explode("-", $sort);
            $sort = [$exploded[0] => $exploded[1]];
            $criteria->setSort($sort);
        } else {
            $criteria->setSort([
                ActivityOrderBy::CREATED_AT => Order::DESC
            ]);
        }

        $isRoleAdmin = $this->isGranted("ROLE_ADMIN");
        if ($isRoleAdmin) {
            $type = $request->query->get('type');
            if (isset($type)) {
                $criteria->setType($type);
            }
        }

        $amountPerPage = $request->query->get('amountPerPage', 12);
        if (isset($amountPerPage) && $amountPerPage > 50) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $qb = $this->activityService->getAllActivityByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($amountPerPage);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $serialized = [];
        foreach ($items as $item) {
            $serialized[] = $this->serializeActivity($item);
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
     * @param Activity $activityItem
     * @return array
     */
    private function serializeActivity(Activity $activityItem)
    {
        $serializedActivityItem = null;

        switch ($activityItem->getType()) {
            case ActivityType::JOINED:
                $serializedActivityItem = [
                    'type' => $activityItem->getType(),
                    'user' => [
                        'username' => $activityItem->getUser()->getUsername(),
                        'displayName' => $activityItem->getUser()->getName(),
                        'avatar' => $this->request->getScheme() . '://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $activityItem->getUser()->getAvatar(),
                    ],
                    'data' => $activityItem->getData()
                ];
                break;
            case ActivityType::ADDED:
                $serializedActivityItem = [
                    'type' => $activityItem->getType(),
                    'user' => [
                        'username' => $activityItem->getUser()->getUsername(),
                        'displayName' => $activityItem->getUser()->getName(),
                        'avatar' => $this->request->getScheme() . '://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $activityItem->getUser()->getAvatar(),
                    ],
                    'data' => $activityItem->getData()
                ];
                break;
            case ActivityType::COMMENT:
                $serializedActivityItem = [
                    'type' => $activityItem->getType(),
                    'user' => [
                        'username' => $activityItem->getUser()->getUsername(),
                        'displayName' => $activityItem->getUser()->getName(),
                        'avatar' => $this->request->getScheme() . '://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $activityItem->getUser()->getAvatar(),
                    ],
                    'data' => $activityItem->getData()
                ];
                break;
            case ActivityType::WATCHLIST:
                $serializedActivityItem = [
                    'type' => $activityItem->getType(),
                    'user' => [
                        'username' => $activityItem->getUser()->getUsername(),
                        'displayName' => $activityItem->getUser()->getName(),
                        'avatar' => $this->request->getScheme() . '://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $activityItem->getUser()->getAvatar(),
                    ],
                    'data' => $activityItem->getData()
                ];
                break;
        }

        return $serializedActivityItem;
    }
}
