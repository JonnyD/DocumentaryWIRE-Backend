<?php

namespace App\Service;

use App\Entity\Documentary;
use App\Enum\ActivityComponent;
use App\Enum\Sync;
use App\Enum\UpdateTimestamps;
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
        $user = $documentary->getAddedBy();

        $latestActivity = $this->getLatestActivity();
        $groupNumber = $latestActivity->getGroupNumber();

        $activity = $this->getAddedActivity($documentary);
        if (!$activity) {
            $latestAddedActivity = $this->getLatestAddedActivityByUser($user);

            if ($latestAddedActivity) {
                $oldGroupNumber = $latestAddedActivity->getGroupNumber();

                $isSameType = $latestActivity->getType() === $latestAddedActivity->getType();
                $isSameUser = $latestActivity->getUser() === $latestAddedActivity->getUser();
                if (!$isSameType || !$isSameUser) {
                    $groupNumber++;
                }

                $activitiesGroupNumber = $this->getActivityByGroupNumberAndUser($oldGroupNumber, $user);
                $isGreaterThanOrEqualTo10 = count($activitiesGroupNumber) >= 10;

                if ($isGreaterThanOrEqualTo10) {
                    $groupNumber++;
                }

                if (!$isGreaterThanOrEqualTo10) {
                    $now = new \DateTime();

                    $editedActivity = [];
                    foreach ($activitiesGroupNumber as $activity) {
                        $createdAt = $activity->getCreatedAt();

                        $isLessThan7DaysApart = ($createdAt->diff($now)->days) < 7;
                        if ($isLessThan7DaysApart) {
                            $activity->setGroupNumber($groupNumber);
                            $editedActivity[] = $activity;
                        }
                    }

                    $this->saveAll($editedActivity);

                }
            } else {
                $groupNumber++;
            }
        }
        $createdAt = new \DateTime();
        $this->addActivity($user, $documentary->getId(), ActivityType::ADDED, ComponentType::DOCUMENTARY, $groupNumber, $createdAt);
    }

    /**
     * @param Watchlist $watchlist
     */
    public function addWatchlistActivity(Watchlist $watchlist)
    {
        $user = $watchlist->getUser();
        $documentary = $watchlist->getDocumentary();

        $latestActivity = $this->getLatestActivity();
        $groupNumber = $latestActivity->getGroupNumber();

        $activity = $this->getWatchlistActivity($user, $documentary);
        if (!$activity) {
            $latestWatchlistActivity = $this->getLatestWatchlistActivityByUser($user);

            if ($latestWatchlistActivity) {
                $oldGroupNumber = $latestWatchlistActivity->getGroupNumber();

                $isSameType = $latestActivity->getType() === $latestWatchlistActivity->getType();
                $isSameUser = $latestActivity->getUser() === $latestWatchlistActivity->getUser();
                if (!$isSameType || !$isSameUser) {
                    $groupNumber++;
                }

                $activitiesGroupNumber = $this->getActivityByGroupNumberAndUser($oldGroupNumber, $user);
                $isGreaterThanOrEqualTo10 = count($activitiesGroupNumber) >= 10;

                if ($isGreaterThanOrEqualTo10) {
                    $groupNumber++;
                }

                if (!$isGreaterThanOrEqualTo10) {
                    $now = new \DateTime();

                    $editedActivity = [];
                    foreach ($activitiesGroupNumber as $activity) {
                        $createdAt = $activity->getCreatedAt();

                        $isLessThan7DaysApart = ($createdAt->diff($now)->days) < 7;
                        if ($isLessThan7DaysApart) {
                            $activity->setGroupNumber($groupNumber);
                            $editedActivity[] = $activity;
                        }
                    }

                    $this->saveAll($editedActivity);
                }
            } else {
                $groupNumber++;
            }

            $createdAt = new \DateTime();
            $this->addActivity($user, $documentary->getId(), ActivityType::WATCHLIST, ComponentType::DOCUMENTARY, $groupNumber, $createdAt);
        }
    }

    /**
     * @param User $user
     */
    public function addJoinedActivity(User $user)
    {
        $latestActivity = $this->getLatestActivity();
        $latestJoinedActivity = $this->getLatestJoinedActivity();

        $groupNumber = $latestActivity->getGroupNumber();
        $oldGroupNumber = $latestJoinedActivity->getGroupNumber();

        $activity = $this->getJoinedActivity($user);
        if (!$activity) {
            $isSameType = $latestActivity->getType() === $latestJoinedActivity->getType();
            if (!$isSameType) {
                $groupNumber++;
            }

            $activitiesGroupNumber = $this->getActivityByGroupNumber($oldGroupNumber);
            $isGreaterThanOrEqualTo20 = count($activitiesGroupNumber) >= 20;

            if ($isGreaterThanOrEqualTo20) {
                $groupNumber++;
            }

            if (!$isGreaterThanOrEqualTo20) {
                $editedActivity = [];
                foreach ($activitiesGroupNumber as $activity) {
                    $activity->setGroupNumber($groupNumber);
                    $editedActivity[] = $activity;
                }
                $this->saveAll($editedActivity);
            }
        }

        $this->addActivity($user, $user->getId(), ActivityType::JOINED, ComponentType::USER, $groupNumber, $user->getActivatedAt());
    }

    /**
     * @param Comment $comment
     */
    public function addCommentActivity(Comment $comment)
    {
        $user = $comment->getUser();

        if ($user) {
            $latestActivity = $this->getLatestActivity();
            $groupNumber = $latestActivity->getGroupNumber();
            $groupNumber++;

            $createdAt = new \DateTime();
            $this->addActivity($user, $comment->getId(), ActivityType::COMMENT, ComponentType::DOCUMENTARY, $groupNumber, $createdAt);
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
     * @param Documentary $documentary
     * @return Activity
     * @throws \Exception
     */
    public function getWatchlistActivity(User $user, Documentary $documentary)
    {
        $criteria = new ActivityCriteria();
        $criteria->setUser($user);
        $criteria->setObjectId($documentary->getId());
        $criteria->setType(ActivityType::WATCHLIST);
        $criteria->setComponent(ComponentType::DOCUMENTARY);

        $watchlistActivity = $this->getActivityByCriteria($criteria);
        return $watchlistActivity;
    }
    /**
     * @param User $user
     * @return Activity
     * @throws \Exception
     */
    public function getJoinedActivity(User $user)
    {
        $criteria = new ActivityCriteria();
        $criteria->setUser($user);
        $criteria->setComponent(ActivityComponent::USER);
        $criteria->setType(ActivityType::JOINED);

        $joinedActivity = $this->getActivityByCriteria($criteria);
        return $joinedActivity;
    }

    /**
     * @param Documentary $documentary
     * @return Activity
     * @throws \Exception
     */
    public function getAddedActivity(Documentary $documentary)
    {
        $criteria = new ActivityCriteria();
        $criteria->setComponent(ActivityComponent::DOCUMENTARY);
        $criteria->setType(ActivityType::ADDED);
        $criteria->setObjectId($documentary->getId());

        $addedActivity = $this->getActivityByCriteria($criteria);
        return $addedActivity;
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
     * @param int $id
     */
    public function removeById(int $id)
    {
        $activity = $this->activityRepository->find($id);
        $this->activityRepository->remove($activity);
    }

    /**
     * @param int $objectId
     */
    public function removeByObjectId(int $objectId)
    {
        $criteria = new ActivityCriteria();
        $criteria->setObjectId($objectId);

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
            ActivityOrderBy::GROUP_NUMBER => Order::DESC,
            ActivityOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->getActivityByCriteria($criteria);
    }

    /**
     * @return Activity
     */
    public function getLatestJoinedActivity()
    {
        $criteria = new ActivityCriteria();
        $criteria->setType(ActivityType::JOINED);
        $criteria->setLimit(1);
        $criteria->setSort([
            ActivityOrderBy::GROUP_NUMBER => Order::DESC,
            ActivityOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->getActivityByCriteria($criteria);
    }

    /**
     * @param User $user
     * @return Activity
     * @throws \Exception
     */
    public function getLatestWatchlistActivityByUser(User $user)
    {
        $criteria = new ActivityCriteria();
        $criteria->setUser($user);
        $criteria->setType(ActivityType::WATCHLIST);
        $criteria->setLimit(1);
        $criteria->setSort([
            ActivityOrderBy::GROUP_NUMBER => Order::DESC,
            ActivityOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->getActivityByCriteria($criteria);
    }

    /**
     * @param User $user
     * @return Activity
     * @throws \Exception
     */
    public function getLatestAddedActivityByUser(User $user)
    {
        $criteria = new ActivityCriteria();
        $criteria->setUser($user);
        $criteria->setType(ActivityType::ADDED);
        $criteria->setLimit(1);
        $criteria->setSort([
            ActivityOrderBy::GROUP_NUMBER => Order::DESC,
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
     * @param int $groupNumber
     * @param User $user
     * @return ArrayCollection|Activity[]
     */
    public function getActivityByGroupNumberAndUser(int $groupNumber, User $user)
    {
        $criteria = new ActivityCriteria();
        $criteria->setUser($user);
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
            $objectId = $activityItem->getObjectId();
            $id = $activityItem->getId();

            $activityObject = new ActivityObject();
            $activityObject->setId($id);
            $activityObject->setObjectId($objectId);
            $activityObject->setType($type);
            $activityObject->setCreatedAt($created);
            $activityObject->setName($name);
            $activityObject->setUsername($username);
            $activityObject->setAvatar($avatar);
            $activityObject->setData($data);
            $activityObject->setGroupNumber($groupNumber);
            $activityObject->setComponent($activityItem->getComponent());

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
     * @param int $id
     * @return Activity|null
     */
    public function getActivityById(int $id)
    {
        return $this->activityRepository->find($id);
    }

    /**
     * @param User $user
     */
    public function mapActivityToUser(User $user)
    {
        $activity = $this->getActivityByEmail($user->getEmail());

        foreach ($activity as $activityItem) {
            $activityItem->setUser($user);
            $this->activityRepository->save($activityItem, Sync::NO);
        }

        $this->activityRepository->flush();
    }

    /**
     * @param Activity $activity
     * @param string $updateTimestamps
     * @param string $sync
     */
    public function save(Activity $activity, string $updateTimestamps = UpdateTimestamps::YES, string $sync = Sync::YES)
    {
        if ($updateTimestamps === UpdateTimestamps::YES) {
            $currentDateTime = new \DateTime();

            if ($activity->getCreatedAt() == null) {
                $activity->setCreatedAt($currentDateTime);
            } else {
                $activity->setUpdatedAt($currentDateTime);
            }
        }

        $this->activityRepository->save($activity, $sync);
    }

    /**
     * @param $activities
     */
    public function saveAll($activities)
    {
        foreach ($activities as $activity) {
            $this->save($activity, UpdateTimestamps::YES, Sync::NO);
        }
        $this->flush();
    }

    public function flush()
    {
        $this->activityRepository->flush();
    }
}