<?php

namespace App\Controller;

use App\Criteria\ActivityCriteria;
use App\Criteria\CommentCriteria;
use App\Criteria\UserCriteria;
use App\Entity\Activity;
use App\Enum\ActivityOrderBy;
use App\Enum\CommentOrderBy;
use App\Enum\CommentStatus;
use App\Enum\Order;
use App\Enum\UserOrderBy;
use App\Service\ActivityService;
use App\Service\CommentService;
use App\Service\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;

class SyncCont extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        ActivityService $activityService,
        CommentService $commentService,
        UserService $userService)
    {
        $this->activityService = $activityService;
        $this->commentService = $commentService;
        $this->userService = $userService;
    }

    /**
     * @FOSRest\Get("/sync", name="sync", options={ "method_prefix" = false })
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        //$this->denyAccessUnlessGranted("ROLE_ADMIN");
        /**
        $watchlistService = $this->getWatchlistService();
        $criteria = new WatchlistCriteria();
        $watchlistService->getWatchlistedByCriteria($criteria);
         **/
        /**
        $criteria = new CommentCriteria();
        $criteria->setSort([
        CommentOrderBy::CREATED_AT => Order::ASC
        ]);
        $criteria->setStatus(CommentStatus::PUBLISH);
        $comments = $this->commentService->getCommentsByCriteria($criteria);
        foreach ($comments as $comment) {
        $this->activityService->addCommentActivity($comment, $comment->getCreatedAt());
        }
         **/

        //$this->updateJoinedActivity();
        $this->fixActivity();

    }

    public function updateJoinedActivity()
    {
        $criteria = new UserCriteria();
        $criteria->setEnabled(true);
        $criteria->setSort([
            UserOrderBy::ENABLED => Order::ASC
        ]);
        $users = $this->userService->getUsersByCriteria($criteria);

        foreach ($users as $user) {
            $this->activityService->addJoinedActivity($user);
        }
    }

    public function fixActivity()
    {
        $criteria = new ActivityCriteria();
        $criteria->setSort([
            ActivityOrderBy::CREATED_AT => Order::ASC
        ]);

        $activity = $this->activityService->getAllActivityByCriteria($criteria);

        $groupNumber = 1;
        $previousType = $activity[0]->getType();
        $previousUserId = $activity[0]->getUser()->getId();
        $activity[0]->setGroupNumber($groupNumber);
        $this->activityService->save($activity[0], false);

        $count = 1;
        foreach ($activity as $act) {
            $increment = true;

            $type = $act->getType();
            $userId = $act->getUser()->getId();

            if ($type == 'joined') {
                if ($previousType == 'joined') {
                    if ($count == 20) {
                        $increment = true;
                        $count = 1;
                    } else {
                        $increment = false;
                        $count++;
                    }
                } else {
                    $increment = true;
                }
            }

            if ($type == 'comment') {
                $increment = true;
            }

            if ($type == 'like') {
                if ($previousType == 'like' && $previousUserId == $userId) {
                    $increment = false;
                } else {
                    $increment = true;
                }
            }

            $previousType = $type;
            $previousUserId = $userId;

            if ($increment) {
                $groupNumber++;
                $act->setGroupNumber($groupNumber);
            } else {
                $act->setGroupNumber($groupNumber);
            }

            $this->activityService->save($act, false);
        }

        $this->activityService->flush();
    }
}