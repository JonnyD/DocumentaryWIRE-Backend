<?php

namespace DW\ActivityBundle\Controller\Publc;

use DW\ActivityBundle\Criteria\ActivityCriteria;
use DW\ActivityBundle\Enum\OrderBy as ActivityOrderBy;
use DW\ActivityBundle\Service\ActivityService;
use DW\BaseBundle\Enum\Order;
use DW\CommentBundle\Criteria\CommentCriteria;
use DW\CommentBundle\Enum\CommentStatus;
use DW\CommentBundle\Enum\OrderBy as CommentOrderBy;
use DW\CommentBundle\Service\CommentService;
use DW\UserBundle\Criteria\UserCriteria;
use DW\UserBundle\Enum\OrderBy as UserOrderBy;
use DW\UserBundle\Service\UserService;
use DW\WatchlistBundle\Criteria\WatchlistCriteria;
use DW\WatchlistBundle\Service\WatchlistService;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends Controller
{
    /**
     * @param Request $request
     * @return Response
    */
    public function listAction(Request $request)
    {
        $activityService = $this->getActivityService();

        $watchlistService = $this->getWatchlistService();
        $criteria = new WatchlistCriteria();
        $watchlistService->getWatchlistedByCriteria($criteria);

        /**
        $commentService = $this->getCommentService();
        $criteria = new CommentCriteria();
        $criteria->setSort([
            CommentOrderBy::CREATED_AT => Order::ASC
        ]);
        $criteria->setStatus(CommentStatus::PUBLISH);
        $comments = $commentService->getCommentsByCriteria($criteria);
        foreach ($comments as $comment) {
            $activityService->addCommentActivity($comment, $comment->getCreatedAt());
        }**/
        /**
        $criteria = new UserCriteria();
        $criteria->setIsActivated(true);
        $criteria->setSort([
            UserOrderBy::ACTIVATED_AT => Order::ASC
        ]);
        $userService = $this->getUserService();
        $users = $userService->getUsersByCritria($criteria);

        foreach ($users as $user) {
            $activityService->addJoinedActivity($user, $user->getCreatedAt());
        }**/

        /**
        $page = $request->query->get('page', 1);

        $criteria = new ActivityCriteria();
        $criteria->setSort([
            ActivityOrderBy::CREATED_AT => Order::ASC
        ]);

        $activityService = $this->getActivityService();
        $activity = $activityService->getAllActivityByCriteria($criteria);

        $groupNumber = 1;
        $previousType = $activity[0]->getType();
        $previousUserId = $activity[0]->getUser()->getId();
        $activity[0]->setGroupNumber($groupNumber);
        $activityService->save($activity[0], false);

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

            $activityService->save($act, false);
        }

        $activityService->flush();


       return $this->render('ActivityBundle:Publc:list.html.twig', [
                'activity' => $activity
        ]);**/
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->get('dw.activity_service');
    }

    /**
     * @return CommentService
     */
    private function getCommentService()
    {
        return $this->get('dw.comment_service');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->get('dw.user_service');
    }

    /**
     * @return WatchlistService
     */
    private function getWatchlistService()
    {
        return $this->get('dw.watchlist_service');
    }
}