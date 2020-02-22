<?php

namespace App\Service;

use App\Entity\Documentary;
use App\Object\Activity\Activity as ActivityObject;
use App\Object\Activity\ActivityItemObject;
use App\Object\Activity\Strategy\DataStrategyContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use App\Criteria\ActivityCriteria;
use App\Entity\Activity;
use App\Enum\ActivityType;
use App\Enum\ComponentType;
use App\Enum\ActivityOrderBy;
use App\Repository\ActivityRepository;
use App\Enum\Order;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Watchlist;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityService
{
    /**
     * @var ActivityRepository
     */
    private $activityRepository;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @param ActivityRepository $activityRepository
     * @param DocumentaryService $documentaryService
     * @param CommentService $commentService
     * @param RequestStack $requestStack
     */
    public function __construct(
        ActivityRepository $activityRepository,
        DocumentaryService $documentaryService,
        CommentService $commentService,
        RequestStack $requestStack)
    {
        $this->activityRepository = $activityRepository;
        $this->documentaryService = $documentaryService;
        $this->commentService = $commentService;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param User $user
     * @param int $objectId
     * @param string $type
     * @param string $component
     * @param int $groupNumber
     * @param \DateTime|null $createdAt
     */
    public function addActivity(
        User $user,
        int $objectId,
        string $type,
        string $component,
        int $groupNumber,
        \DateTime $createdAt = null)
    {
        $activity = new Activity();
        $activity->setUser($user);
        $activity->setObjectId($objectId);
        $activity->setType($type);
        $activity->setComponent($component);
        $activity->setGroupNumber($groupNumber);
        $activity->setCreatedAt($createdAt);

        $this->activityRepository->save($activity);
    }

    /**
     * @param Activity $activity
     */
    public function removeActivity(Activity $activity)
    {
        $this->activityRepository->remove($activity);
    }

    public function addAddedActivity(Documentary $documentary)
    {
        //@TODO
    }

    /**
     * @param Watchlist $watchlist
     */
    public function addWatchlistActivity(Watchlist $watchlist)
    {
        $user = $watchlist->getUser();
        $documentary = $watchlist->getDocumentary();

        $criteria = new ActivityCriteria();
        $criteria->setUser($user);
        $criteria->setObjectId($documentary->getId());
        $criteria->setType(ActivityType::WATCHLIST);
        $criteria->setComponent(ComponentType::DOCUMENTARY);

        $activity = $this->getActivityByCriteria($criteria);

        if ($activity) {
            $activity->setCreatedAt(new \DateTime());
            $this->activityRepository->save($activity);
        } else {
            $latestActivity = $this->getLatestActivity();
            $groupNumber = $latestActivity->getGroupNumber();

            if ($latestActivity->getType() != ActivityType::WATCHLIST) {
                $groupNumber++;
            } else {
                if ($latestActivity->getUser() == $user) {
                    $activityGroupNumber = $this->getActivityByGroupNumber($groupNumber);
                    if (count($activityGroupNumber) >= 20) {
                        $groupNumber++;
                    }
                }
            }

            $this->addActivity($user, $documentary->getId(), ActivityType::WATCHLIST, ComponentType::DOCUMENTARY, $groupNumber);
        }
    }

    /**
     * @param Watchlist $watchlist
     */
    public function removeWatchlistActivity(Watchlist $watchlist)
    {
        $user = $watchlist->getUser();
        $documentary = $watchlist->getDocumentary();

        $criteria = new ActivityCriteria();
        $criteria->setUser($user);
        $criteria->setObjectId($documentary->getId());
        $criteria->setType(ActivityType::WATCHLIST);
        $criteria->setComponent(ComponentType::DOCUMENTARY);

        $activity = $this->getActivityByCriteria($criteria);

        $this->activityRepository->remove($activity);
    }

    /**
     * @param User $user
     */
    public function addJoinedActivity(User $user)
    {
        $latestActivity = $this->getLatestActivity();
        if ($latestActivity) {
            $groupNumber = $latestActivity->getGroupNumber();

            if ($latestActivity->getType() != ActivityType::JOINED) {
                $groupNumber++;
            } else {
                $activityGroupNumber = $this->getActivityByGroupNumber($groupNumber);
                if (count($activityGroupNumber) >= 20) {
                    $groupNumber++;
                }
            }
        } else {
            $groupNumber = 1;
        }

        $this->addActivity($user, $user->getId(), ActivityType::JOINED, ComponentType::USER, $groupNumber, $user->getActivatedAt());
    }

    /**
     * @param Comment $comment
     */
    public function addCommentActivity(Comment $comment, \Datetime $createdAt)
    {
        $user = $comment->getUser();

        if ($user) {
            $latestActivity = $this->getLatestActivity();
            $groupNumber = $latestActivity->getGroupNumber();
            $groupNumber++;

            $this->addActivity($user, $comment->getId(), ActivityType::COMMENT, ComponentType::DOCUMENTARY, $groupNumber, $createdAt);
        }
    }
    /**
     * @param Comment $comment
     */
    public function removeCommentActivity(Comment $comment)
    {
        $criteria = new ActivityCriteria();
        $criteria->setObjectId($comment->getId());
        $criteria->setType(ActivityType::COMMENT);
        $criteria->setComponent(ComponentType::DOCUMENTARY);

        $activity = $this->getActivityByCriteria($criteria);

        $this->activityRepository->remove($activity);
    }

    /**
     * @return Activity
     */
    public function getLatestActivity()
    {
        $criteria = new ActivityCriteria();
        $criteria->setLimit(1);
        $criteria->setSort([
            ActivityOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->getActivityByCriteria($criteria);
    }

    /**
     * @param User $user
     * @return Activity[]
     */
    public function getActivityByUser(User $user)
    {
        $criteria = new ActivityCriteria();
        $criteria->setUser($user);
        $criteria->setSort([
            ActivityOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->getAllActivityByCriteria($criteria);
    }

    /**
     * @param int $groupNumber
     * @return ArrayCollection|Activity[]
     */
    public function getActivityByGroupNumber(int $groupNumber)
    {
        $criteria = new ActivityCriteria();
        $criteria->setGroupNumber($groupNumber);
        $criteria->setSort([
            ActivityOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->activityRepository->findAllByCriteria($criteria);
    }

    /**
     * @param ActivityCriteria $criteria
     * @return QueryBuilder
     */
    public function getAllActivityByCriteriaQueryBuilder(ActivityCriteria $criteria)
    {
        return $this->activityRepository->findAllByCriteriaQueryBuilder($criteria);
    }

    /**
     * @param ActivityCriteria $criteria
     * @return ArrayCollection|Activity[]
     */
    public function getAllActivityByCriteria(ActivityCriteria $criteria)
    {
        return $this->activityRepository->findAllByCriteria($criteria);
    }

    /**
     * @param ActivityCriteria $criteria
     * @return Activity
     */
    public function getActivityByCriteria(ActivityCriteria $criteria)
    {
        return $this->activityRepository->findByCriteria($criteria);
    }

    /**
     * @return array
     */
    public function getRecentActivityForWidget()
    {
        $criteria = new ActivityCriteria();
        $criteria->setLimit(300);
        $criteria->setSort([
            ActivityOrderBy::GROUP_NUMBER => Order::DESC,
            ActivityOrderBy::CREATED_AT => Order::DESC
        ]);

        $activity = $this->activityRepository->findAllByCriteria($criteria);
        $activityArray = $this->convertActivityToArray($activity);

        return $activityArray;
    }

    /**
     * @return array
     */
    public function getRecentActivityCommentsForWidget()
    {
        $criteria = new ActivityCriteria();
        $criteria->setType(ActivityType::COMMENT);
        $criteria->setLimit(20);
        $criteria->setSort([
            ActivityOrderBy::CREATED_AT => Order::DESC
        ]);

        $activity = $this->activityRepository->findAllByCriteria($criteria);
        $activityArray = $this->convertActivityToArray($activity);

        return $activityArray;
    }

    /**
     * @return array
     */
    public function getRecentActivityWatchlistedForWidget()
    {
        $criteria = new ActivityCriteria();
        $criteria->setType(ActivityType::WATCHLIST);
        $criteria->setLimit(20);
        $criteria->setSort([
            ActivityOrderBy::CREATED_AT => Order::DESC
        ]);

        $activity = $this->activityRepository->findAllByCriteria($criteria);
        $activityArray = $this->convertActivityToArray($activity);

        return $activityArray;
    }

    /**
     * @param ArrayCollection|Activity[] $activity
     * @return array
     */
    private function convertActivityToArray(array $activity)
    {
        $activityMap = [];

        $previousGroupNumber = 0;
        /** @var Activity $activityEntity */
        foreach ($activity as $activityItem) {
            $groupNumber = $activityItem->getGroupNumber();

            if (array_key_exists($groupNumber, $activityMap) != null) {
                $activityItemObject = $activityMap[$groupNumber];
            } else {
                $activityItemObject = new ActivityItemObject();
            }

            $type = $activityItem->getType();
            $created = $activityItem->getCreatedAt();

            $activityItemObject->setType($type);
            $activityItemObject->setCreated($created);

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

            $hasChildren = ActivityType::hasChildren($type);
            if ($hasChildren) {
                if ($groupNumber != $previousGroupNumber) {
                    $activityItemObject->setParent($activityObject);
                } else {
                    $activityItemObject->addChild($activityObject);
                }
            } else {
                $activityItemObject->setParent($activityObject);
            }

            $activityMap[$groupNumber] = $activityItemObject;

            $previousGroupNumber = $groupNumber;
        }

        $display = [];
        /** @var ActivityItemObject $value */
        foreach ($activityMap as $key => $value) {
            $display[] = $value->toArray();
        }

        return $display;
    }

    /**
     * @param string $email
     * @return Activity[]|ArrayCollection
     */
    public function getActivityByEmail(string $email)
    {
        $criteria = new ActivityCriteria();
        $criteria->setEmail($email);

        return $this->activityRepository->findAllByCriteria($criteria);
    }

    /**
     * @param User $user
     */
    public function mapActivityToUser(User $user)
    {
        $activity = $this->getActivityByEmail($user->getEmail());

        foreach ($activity as $activityItem) {
            $activityItem->setUser($user);
            $this->activityRepository->save($activityItem, false);
        }

        $this->activityRepository->flush();
    }

    /**
     * @param Activity $activity
     * @param bool $sync
     */
    public function save(Activity $activity, bool $sync = true)
    {
        $this->activityRepository->save($activity, $sync);
    }

    public function flush()
    {
        $this->activityRepository->flush();
    }
}