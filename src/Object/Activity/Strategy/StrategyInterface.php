<?php

namespace App\Object\Activity\Strategy;

use App\Entity\Activity;

interface StrategyInterface
{
    public function createData(Activity $activity);
}