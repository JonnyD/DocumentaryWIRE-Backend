<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Entity\Watchlist;
use App\Service\ActivityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class WatchlistDeletedSubscriber implements EventSubscriberInterface
{
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @param ActivityService $activityService
     */
    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['watchlistDeletedActivity', EventPriorities::POST_WRITE],
        ];
    }

    public function watchlistDeletedActivity(ViewEvent $event)
    {
        $watchlist = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$watchlist instanceof Watchlist || Request::METHOD_DELETE !== $method) {
            return;
        }

        $this->activityService->removeWatchlistActivity($watchlist);
    }
}