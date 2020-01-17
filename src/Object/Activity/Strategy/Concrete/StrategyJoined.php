<?php

namespace App\Object\Activity\Strategy\Concrete;

use App\Entity\Activity;
use App\Object\Activity\ActivityChild;
use App\Object\Activity\ActivityParent;
use App\Object\Activity\Data\JoinedData;
use App\Object\Activity\Data\WatchlistData;
use App\Object\Activity\Strategy\StrategyInterface;
use App\Service\DocumentaryService;

class StrategyJoined implements StrategyInterface
{
    /**
     * @var int
     */
    private $groupNumber;

    /**
     * @var int
     */
    private $previousGroupNumber;

    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @param int $groupNumber
     * @param int $previousGroupNumber
     */
    public function __construct(
        int $groupNumber,
        int $previousGroupNumber)
    {
        $this->groupNumber = $groupNumber;
        $this->previousGroupNumber = $previousGroupNumber;
    }

    /**
     * @param Activity $activityEntity
     * @return array
     */
    public function createActivity(Activity $activityEntity)
    {
        $user = $activityEntity->getUser();

        $username = $user->getUsername();
        $name = $user->getName();
        $avatar = $user->getAvatar();

        $joinedData = new JoinedData();

        $tempActivityArray = [];
        if ($this->groupNumber != $this->previousGroupNumber) {
            $parent = new ActivityParent();
            $parent->setData($joinedData);
            $parent->setName($name);
            $parent->setAvatar($avatar);
            $parent->setUsername($username);

            $tempActivityArray['parent'] = $parent->toArray();
        } else {

            $child = new ActivityChild();
            $child->setData($joinedData);
            $child->setName($name);
            $child->setAvatar($avatar);
            $child->setUsername($username);

            $tempActivityArray['child'][] = $child->toArray();
        }

        return $tempActivityArray;
    }
}