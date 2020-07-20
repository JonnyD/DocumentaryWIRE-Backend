<?php

namespace App\EventListener;

use App\Event\WatchlistEvent;
use App\Event\WatchlistEvents;
use App\Service\ActivityService;
use App\Service\DocumentaryService;
use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WatchlistActivityListener implements EventSubscriberInterface
{
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @param ActivityService $activityService
     */
    public function __construct(
        ActivityService $activityService
    )
    {
        $this->activityService = $activityService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            WatchlistEvents::WATCHLIST_CREATED => "onWatchlistCreated"
        );
    }

    /**
     * @param WatchlistEvent $watchlistEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onWatchlistCreated(WatchlistEvent $watchlistEvent)
    {
        $watchlist = $watchlistEvent->getWatchlist();
        $this->activityService->addWatchlistActivity($watchlist);
    }
}