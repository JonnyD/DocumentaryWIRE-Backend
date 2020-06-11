<?php

namespace App\Controller;

use App\Criteria\ActivityCriteria;
use App\Criteria\CommentCriteria;
use App\Criteria\DocumentaryCriteria;
use App\Criteria\UserCriteria;
use App\Criteria\WatchlistCriteria;
use App\Entity\Activity;
use App\Entity\DocumentaryVideoSource;
use App\Entity\Email;
use App\Entity\Follow;
use App\Enum\ActivityOrderBy;
use App\Enum\ActivityType;
use App\Enum\CommentOrderBy;
use App\Enum\CommentStatus;
use App\Enum\DocumentaryStatus;
use App\Enum\IsParent;
use App\Enum\Order;
use App\Enum\UserOrderBy;
use App\Service\ActivityService;
use App\Service\CategoryService;
use App\Service\CommentService;
use App\Service\DocumentaryService;
use App\Service\DocumentaryVideoSourceService;
use App\Service\EmailService;
use App\Service\FollowService;
use App\Service\UserService;
use App\Service\VideoSourceService;
use App\Service\WatchlistService;
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

    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var WatchlistService
     */
    private $watchlistService;

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @var DocumentaryVideoSourceService
     */
    private $documentaryVideoSourceService;

    public function __construct(
        ActivityService $activityService,
        CommentService $commentService,
        UserService $userService,
        CategoryService $categoryService,
        DocumentaryService $documentaryService,
        WatchlistService $watchlistService,
        EmailService $emailService,
        DocumentaryVideoSourceService $documentaryVideoSourceService)
    {
        $this->activityService = $activityService;
        $this->commentService = $commentService;
        $this->userService = $userService;
        $this->categoryService = $categoryService;
        $this->documentaryService = $documentaryService;
        $this->watchlistService = $watchlistService;
        $this->emailService = $emailService;
        $this->documentaryVideoSourceService = $documentaryVideoSourceService;
    }

    /**
     * @FOSRest\Get("/sync", name="sync", options={ "method_prefix" = false })
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

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
        //$this->fixActivity();
        //$this->updateCommentCountForDocumentaries();
        //$this->updateDocumentaryCountForCategories();
        //$this->updateWatchlistCountForDocumentaries();
        //$this->updateViewsDate();
        //$this->updateYearFrom0ToNull();
        //$this->updateIsParent();
        //$this->updateSubscriptionKeys();
        $this->updateCreatedAtForDocumentaryVideoSources();
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
            echo $count;
            $increment = true;

            $type = $act->getType();
            $userId = $act->getUser()->getId();

            if ($type == ActivityType::JOINED) {
                if ($previousType == ActivityType::JOINED) {
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

            if ($type == ActivityType::COMMENT) {
                $increment = true;
            }

            if ($type == ActivityType::WATCHLIST) {
                if ($previousType == ActivityType::WATCHLIST && $previousUserId == $userId) {
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

    public function updateDocumentaryCountForCategories()
    {
        $categories = $this->categoryService->getAllCategories();

        $updatedCategories = [];
        foreach ($categories as $category) {
            $documentaryCriteria = new DocumentaryCriteria();
            $documentaryCriteria->setStatus(DocumentaryStatus::PUBLISHED);
            $documentaryCriteria->setCategory($category);
            $documentaries = $this->documentaryService->getDocumentariesByCriteria($documentaryCriteria);

            $documentaryCount = count($documentaries);
            $category->setDocumentaryCount($documentaryCount);
            $updatedCategories[] = $category;
        }

        foreach ($updatedCategories as $updatedCategory) {
            $this->categoryService->save($updatedCategory);
        }
    }

    public function updateCommentCountForDocumentaries()
    {
        $documentaries = $this->documentaryService->getAllDocumentaries();

        $updatedDocumentaries = [];
        foreach ($documentaries as $documentary) {
            $commentCriteria = new CommentCriteria();
            $commentCriteria->setDocumentary($documentary);
            $commentCriteria->setStatus(CommentStatus::PUBLISH);
            $comments = $this->commentService->getCommentsByCriteria($commentCriteria);

            $commentCount = count($comments);
            $documentary->setCommentCount($commentCount);
            $updatedDocumentaries[] = $documentary;
        }

        foreach ($updatedDocumentaries as $updatedDocumentary) {
            $this->documentaryService->save($updatedDocumentary, false);
        }

        $this->documentaryService->flush();
    }

    public function updateWatchlistCountForDocumentaries()
    {
        $documentaries = $this->documentaryService->getAllDocumentaries();

        $updatedDocumentaries = [];
        foreach ($documentaries as $documentary) {
            $watchlistCriteria = new WatchlistCriteria();
            $watchlistCriteria->setDocumentary($documentary);
            $watchlists = $this->watchlistService->getWatchlistsByCriteria($watchlistCriteria);

            $watchlistCount = count($watchlists);
            $documentary->setWatchlistCount($watchlistCount);
            $updatedDocumentaries[] = $documentary;
        }

        foreach ($updatedDocumentaries as $updatedDocumentary) {
            $this->documentaryService->save($updatedDocumentary);
        }
    }

    /**
     * @TODO tombstone
     */
    private function fixActivityData()
    {
        $criteria = new ActivityCriteria();
        $activities = $this->activityService->getAllActivityByCriteria($criteria);

        foreach ($activities as $activity) {
            $data = $activity->getData();

            if ($activity->getType() === ActivityType::WATCHLIST) {
                $oldThumbnailPath = $data['documentaryThumbnail'];

                if (strpos($oldThumbnailPath, "documentary/")) {
                    $exploded = explode("documentary/", $oldThumbnailPath);
                    $data['documentaryThumbnail'] = $exploded[1];
                } else if (strpos($oldThumbnailPath, "/") != null) {
                    $exploded = explode("/", $oldThumbnailPath);
                    $data['documentaryThumbnail'] = $exploded[1];
                }

                $activity->setData($data);
                $this->activityService->save($activity);
            }
        }
    }

    private function updateViewsDate()
    {
        $documentaries = $this->documentaryService->getAllDocumentaries();

        foreach ($documentaries as $documentary) {
            $documentary->setViewsDate(new \DateTime());

            $this->documentaryService->save($documentary, false);
        }

        $this->documentaryService->flush();
    }

    private function updateYearFrom0ToNull()
    {
        $documentaries = $this->documentaryService->getAllDocumentaries();

        foreach ($documentaries as $documentary) {
            if ($documentary->getYearFrom() === 0) {
                $documentary->setYearFrom(null);

                $this->documentaryService->save($documentary);
            }
        }

        $this->documentaryService->flush();
    }

    public function updateIsParent()
    {
        $documentaries = $this->documentaryService->getAllDocumentaries();

        foreach ($documentaries as $documentary) {
            $documentary->setIsParent(IsParent::YES);

            $this->documentaryService->save($documentary, false);
        }

        $this->documentaryService->flush();
    }

    private function updateSubscriptionKeys()
    {
        $emails = $this->emailService->getAllEmails();
        $this->emailService->updateSubscriptionKeysForEmailsUsingModulos($emails, 100);
    }

    private function updateCreatedAtForDocumentaryVideoSources()
    {
        $this->documentaryVideoSourceService->updateCreatedAtForDocumentaryVideoSources();
    }
}