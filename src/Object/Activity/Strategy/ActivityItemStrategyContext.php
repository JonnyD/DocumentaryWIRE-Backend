<?php

namespace App\Object\Activity\Strategy;

use App\Entity\Activity;
use App\Enum\ActivityType;
use App\Object\Activity\Strategy\Concrete\StrategyAdded;
use App\Object\Activity\Strategy\Concrete\StrategyComment;
use App\Object\Activity\Strategy\Concrete\StrategyJoined;
use App\Object\Activity\Strategy\Concrete\StrategyWatchlist;
use App\Service\CommentService;
use App\Service\DocumentaryService;

class ActivityItemStrategyContext
{
    /**
     * @var StrategyInterface|null
     */
    private $strategy = null;

    /**
     * @param string $type
     * @param DocumentaryService $documentaryService
     * @param CommentService $commentService
     * @param int $groupNumber
     * @param int $previousGroupNumber
     */
    public function __construct(
        string $type,
        DocumentaryService $documentaryService,
        CommentService $commentService,
        int $groupNumber = 0,
        int $previousGroupNumber = 0)
    {
        switch ($type) {
            case ActivityType::LIKE:
                $this->strategy = new StrategyWatchlist(
                    $groupNumber,
                    $previousGroupNumber,
                    $documentaryService);
            break;
            case ActivityType::COMMENT:
                $this->strategy = new StrategyComment(
                    $groupNumber,
                    $previousGroupNumber,
                    $commentService);
            break;
            case ActivityType::JOINED:
                $this->strategy = new StrategyJoined(
                    $groupNumber,
                    $previousGroupNumber);
            break;
            case ActivityType::ADDED:
                $this->strategy = new StrategyAdded(
                    $groupNumber,
                    $previousGroupNumber,
                    $documentaryService);
            break;
        }
    }

    /**
     * @param Activity $activityEntity
     * @return mixed
     */
    public function createActivity(Activity $activityEntity)
    {
        return $this->strategy->createActivity($activityEntity);
    }
}