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
use Symfony\Component\HttpFoundation\Request;

class DataStrategyContext
{
    /**
     * @var StrategyInterface|null
     */
    private $strategy = null;

    public function __construct(
        string $type,
        Request $request,
        DocumentaryService $documentaryService,
        CommentService $commentService)
    {
        switch ($type) {
            case ActivityType::LIKE:
                $this->strategy = new StrategyWatchlist(
                    $request,
                    $documentaryService
                );
            break;
            case ActivityType::COMMENT:
                $this->strategy = new StrategyComment(
                    $request,
                    $commentService
                );
            break;
            case ActivityType::JOINED:
                $this->strategy = new StrategyJoined();
            break;
            case ActivityType::ADDED:
                $this->strategy = new StrategyAdded(
                    $documentaryService
                );
            break;
        }
    }

    /**
     * @param Activity $activityEntity
     * @return mixed
     */
    public function createData(Activity $activityEntity)
    {
        return $this->strategy->createData($activityEntity);
    }
}