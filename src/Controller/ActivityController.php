<?php

namespace App\Controller;

use App\Criteria\ActivityCriteria;
use App\Entity\Activity;
use App\Enum\ActivityOrderBy;
use App\Enum\Order;
use App\Service\ActivityService;
use App\Service\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var Requestt
     */
    private $request;

    public function __construct(
        ActivityService $activityService,
        UserService $userService,
        RequestStack $requestStack)
    {
        $this->activityService = $activityService;
        $this->userService = $userService;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @FOSRest\Get("/activity", name="get_activity", options={ "method_prefix" = false })
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        $criteria = new ActivityCriteria();

        $username = $request->query->get('user');
        if (isset($username)) {
            $user = $this->userService->getUserByUsername($username);
            $criteria->setUser($user);
        }

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

        $qb = $this->activityService->getAllActivityByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(6);
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

        return new JsonResponse($data, 200, array('Access-Control-Allow-Origin'=> '*'));
    }

    /**
     * @FOSRest\Get("/activity-for-widget", name="get_activity_for_widget", options={ "method_prefix" = false })
     *
     * @return JsonResponse
     */
    public function listForWidgetAction()
    {
        $activities = $this->activityService->getRecentActivityForWidget();

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        return new JsonResponse($activities, 200, $headers);
    }

    private function serializeActivity(Activity $activity)
    {
        return [
            'type' => $activity->getType(),
            'component' => $activity->getComponent(),
            'objectId' => $activity->getObjectId(),
            'data' => $activity->getData(),
            'groupNumber' => $activity->getGroupNumber(),
            'user' => [
                'username' => $activity->getUser()->getUsername(),
                'name' => $activity->getUser()->getName(),
                'avatar' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $activity->getUser()->getAvatar()
            ]
        ];
    }
}