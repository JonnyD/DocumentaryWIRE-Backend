<?php

namespace App\Service;

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
     * @param ActivityRepository $activityRepository
     * @param RequestStack $requestStack
     */
    public function __construct(
        ActivityRepository $activityRepository,
        RequestStack $requestStack)
    {
        $this->activityRepository = $activityRepository;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param User $user
     * @param int $objectId
     * @param string $type
     * @param string $component
     * @param array $data
     * @param int $groupNumber
     */
    public function addActivity(User $user, int $objectId, string $type, string $component, array $data, int $groupNumber, \DateTime $createdAt = null)
    {
        $activity = new Activity();
        $activity->setUser($user);
        $activity->setObjectId($objectId);
        $activity->setType($type);
        $activity->setComponent($component);
        $activity->setData($data);
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
        $criteria->setType(ActivityType::LIKE);
        $criteria->setComponent(ComponentType::DOCUMENTARY);

        $activity = $this->getActivityByCriteria($criteria);

        if ($activity) {
            $activity->setCreatedAt(new \DateTime());
            $this->activityRepository->save($activity);
        } else {
            $data = [
                "documentaryId" => $documentary->getId(),
                "documentaryTitle" => $documentary->getTitle(),
                "documentaryExcerpt" => $documentary->getSummary(),
                //"documentaryThumbnail" => $documentary->getPoster(),
                "documentarySlug" => $documentary->getSlug()
            ];

            $latestActivity = $this->getLatestActivity();
            $groupNumber = $latestActivity->getGroupNumber();

            if ($latestActivity->getType() != ActivityType::LIKE) {
                $groupNumber++;
            } else {
                if ($latestActivity->getUser() == $user) {
                    $activityGroupNumber = $this->getActivityByGroupNumber($groupNumber);
                    if (count($activityGroupNumber) >= 20) {
                        $groupNumber++;
                    }
                }
            }

            $this->addActivity($user, $documentary->getId(), ActivityType::LIKE, ComponentType::DOCUMENTARY, $data, $groupNumber);
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
        $criteria->setType(ActivityType::LIKE);
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

        $this->addActivity($user, $user->getId(), ActivityType::JOINED, ComponentType::USER, [], $groupNumber);
    }

    /**
     * @param Comment $comment
     */
    public function addCommentActivity(Comment $comment, \Datetime $createdAt)
    {
        $documentary = $comment->getDocumentary();
        $user = $comment->getUser();

        if ($user) {
            $data = [
                "documentaryId" => $documentary->getId(),
                "documentaryTitle" => $documentary->getTitle(),
                //"documentaryThumbnail" => $documentary->getPoster(),
                "documentarySlug" => $documentary->getSlug(),
                "comment" => $comment->getCommentText()
            ];

            $latestActivity = $this->getLatestActivity();
            $groupNumber = $latestActivity->getGroupNumber();
            $groupNumber++;

            $this->addActivity($user, $comment->getId(), ActivityType::COMMENT, ComponentType::DOCUMENTARY, $data, $groupNumber, $createdAt);
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
        $criteria->setType(ActivityType::LIKE);
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
        $activityArray = array();

        $previousGroupNumber = null;
        /** @var Activity $activityItem */
        foreach ($activity as $activityItem) {
            $type = $activityItem->getType();
            $groupNumber = $activityItem->getGroupNumber();
            $user = $activityItem->getUser();
            $name = $user->getName();
            $avatar = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $user->getAvatar();
            $data = $activityItem->getData();
            $created = $activityItem->getCreatedAt();

            $activityArray[$groupNumber]['type'] = $type;
            $activityArray[$groupNumber]['created'] = $created;

            if ($type == "like") {
                if ($groupNumber != $previousGroupNumber) {
                    $data['documentaryThumbnail'] = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $data['documentaryThumbnail'];
                    $activityArray[$groupNumber]['parent']['data'] = $data;
                    $activityArray[$groupNumber]['parent']['user']['name'] = $name;
                    $activityArray[$groupNumber]['parent']['user']['avatar'] = $avatar;
                } else {
                    $data['documentaryThumbnail'] = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $data['documentaryThumbnail'];
                    $child['data'] = $data;
                    $child['user']['name'] = $name;
                    $child['user']['avatar'] = $avatar;
                    $activityArray[$groupNumber]['child'][] = $child;
                }
            } else if ($type == "comment") {
                $activityArray[$groupNumber]['parent']['user']['name'] = $name;
                $activityArray[$groupNumber]['parent']['user']['avatar'] = $avatar;
                $activityArray[$groupNumber]['parent']['data'] = $data;
            } else if ($type == "joined") {
                if ($groupNumber != $previousGroupNumber) {
                    $activityArray[$groupNumber]['parent']['user']['name'] = $name;
                    $activityArray[$groupNumber]['parent']['user']['avatar'] = $avatar;
                } else {
                    $child['user']['name'] = $name;
                    $child['user']['avatar'] = $avatar;#
                    $activityArray[$groupNumber]['child'][] = $child;
                }
            } else if ($type == "added") {
                if ($groupNumber != $previousGroupNumber) {
                    $activityArray[$groupNumber]['parent']['data'] = $data;
                    $activityArray[$groupNumber]['parent']['user']['name'] = $name;
                    $activityArray[$groupNumber]['parent']['user']['avatar'] = $avatar;
                } else {
                    $child['data'] = $data;
                    $child['user']['name'] = $name;
                    $child['user']['avatar'] = $avatar;
                    $activityArray[$groupNumber]['child'][] = $child;
                }
            }

            $previousGroupNumber = $groupNumber;
        }

        return $activityArray;
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