<?php

namespace App\Hydrator;

use App\Entity\Activity;
use App\Entity\Season;
use App\Object\Activity\Strategy\DataStrategyContext;
use App\Service\CommentService;
use App\Service\DocumentaryService;
use Symfony\Component\HttpFoundation\Request;
use App\Object\Activity\Activity as ActivityObject;

class SeasonHydrator implements HydratorInterface
{
    /**
     * @var Season
     */
    private $season;

    /**
     * @param Season $season
     */
    public function __construct(
        Season $season)
    {
        $this->season = $season;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->season->getId(),
            'seasonNumber' => $this->season->getSeasonNumber(),
            'summary' => $this->season->getSummary()
        ];

        return $array;
    }
}