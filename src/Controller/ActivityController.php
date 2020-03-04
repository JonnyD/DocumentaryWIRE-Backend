<?php

namespace App\Controller;

use App\Criteria\ActivityCriteria;
use App\Entity\Activity;
use App\Enum\ActivityOrderBy;
use App\Enum\ActivityType;
use App\Enum\Order;
use App\Object\Activity\Strategy\DataStrategyContext;
use App\Service\ActivityService;
use App\Service\CommentService;
use App\Service\DocumentaryService;
use App\Service\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityController extends BaseController implements ClassResourceInterface
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
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @var Request
     */
    private $request;

    public function __construct(
        ActivityService $activityService,
        UserService $userService,
        DocumentaryService $documentaryService,
        CommentService $commentService,
        RequestStack $requestStack)
    {
        $this->activityService = $activityService;
        $this->userService = $userService;
        $this->documentaryService = $documentaryService;
        $this->commentService = $commentService;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @FOSRest\Get("/activity", name="get_activity", options={ "method_prefix" = false })
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $show = $request->query->get('show');

        if (isset($show) && $show === 'widget') {
            $activities = $this->activityService->getRecentActivityForWidget();
            return $this->createApiResponse($activities, 200, array('Access-Control-Allow-Origin'=> '*'));
        }
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

            $hasOrderBy = ActivityOrderBy::hasOrderBy($exploded[0]);
            if (!$hasOrderBy) {
                return $this->createApiResponse('Order by ' . $exploded[0] . ' does not exist', 404);
            }

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
        $pagerfanta->setMaxPerPage(50);
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
     * @FOSRest\Get("/activity-for-widget", name="get_activity_for_widget", options={ "method_prefix" = false })
     *
     * @return JsonResponse
     */
    public function listForWidgetAction()
    {
        $activities = $this->activityService->getRecentActivityForWidget();
        return $this->createApiResponse($activities, 200);
    }

    /**
     * @param Activity $activity
     * @return array
     */
    private function serializeActivity(Activity $activity)
    {
        $type = $activity->getType();
        $createdAt = $activity->getCreatedAt();

        $dataStrategyContext = new DataStrategyContext(
            $type,
            $this->request,
            $this->documentaryService,
            $this->commentService);
        $data = $dataStrategyContext->createData($activity);

        $user = $activity->getUser();
        $name = $user->getName();
        $avatar = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $user->getAvatar();
        $username = $user->getUsername();

        $activityObject = new \App\Object\Activity\Activity();
        $activityObject->setName($name);
        $activityObject->setUsername($username);
        $activityObject->setAvatar($avatar);
        $activityObject->setData($data);
        $activityObject->setType($type);
        $activityObject->setCreatedAt($createdAt);
        $activityObject->setComponent($activity->getComponent());
        $activityObject->setGroupNumber($activity->getGroupNumber());

        return $activityObject->toArray();
    }
}