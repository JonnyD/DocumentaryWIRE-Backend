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
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Object\Activity\Activity as ActivityObject;

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
     * @var CommentService
     */
    private $commentService;

    /**
     * @var null|Request
     */
    private $request;

    /**
     * @param ActivityService $activityService
     * @param DocumentaryService $documentaryService
     * @param CommentService $commentService
     * @param RequestStack $requestStack
     */
    public function __construct(
        ActivityService $activityService,
        DocumentaryService $documentaryService,
        CommentService $commentService,
        RequestStack $requestStack)
    {
        $this->activityService = $activityService;
        $this->documentaryService = $documentaryService;
        $this->commentService = $commentService;
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

        $type = $request->query->get('type');
        $isRoleAdmin = $this->isGranted("ROLE_ADMIN");
        if (isset($type) && !$isRoleAdmin) {
            return $this->createApiResponse('Not Authorized to view types', 401);
        } else if (isset($type) && $isRoleAdmin) {
            if (!ActivityType::hasType($type)) {
                return $this->createApiResponse('Type ' . $type . ' does not exist', 404);
            }
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
        $type = $activityItem->getType();
        $createdAt = $activityItem->getCreatedAt();

        $dataStrategyContext = new DataStrategyContext(
            $type,
            $this->request,
            $this->documentaryService,
            $this->commentService);
        $data = $dataStrategyContext->createData($activityItem);

        $user = $activityItem->getUser();
        $name = $user->getName();
        $avatar = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $user->getAvatar();
        $username = $user->getUsername();

        $activityObject = new ActivityObject();
        $activityObject->setName($name);
        $activityObject->setUsername($username);
        $activityObject->setAvatar($avatar);
        $activityObject->setData($data);
        $activityObject->setType($type);
        $activityObject->setCreatedAt($createdAt);

        return $activityObject->toArray();
    }
}
