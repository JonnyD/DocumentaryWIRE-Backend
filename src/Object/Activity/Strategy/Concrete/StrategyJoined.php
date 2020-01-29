<?php

namespace App\Object\Activity\Strategy\Concrete;

use App\Entity\Activity;
use App\Object\Activity\ActivityChild;
use App\Object\Activity\ActivityParent;
use App\Object\Activity\Data\Data;
use App\Object\Activity\Data\JoinedData;
use App\Object\Activity\Data\WatchlistData;
use App\Object\Activity\Strategy\StrategyInterface;
use App\Service\DocumentaryService;

class StrategyJoined implements StrategyInterface
{
    /**
     * StrategyJoined constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param Activity $activityEntity
     * @return Data
     */
    public function createData(Activity $activityEntity)
    {
        $joinedData = new JoinedData();
        return $joinedData;
    }
}