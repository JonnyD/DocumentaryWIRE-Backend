<?php

namespace App\EventListener;

use App\Event\CommentEvent;
use App\Event\CommentEvents;
use App\Event\UserEvent;
use App\Event\UserEvents;
use App\Event\WatchlistEvent;
use App\Event\WatchlistEvents;
use App\Service\ActivityService;
use App\Service\CommentService;
use App\Service\DocumentaryService;
use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddorDeleteActivityListener implements EventSubscriberInterface
{
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @param ActivityService $activityService
     * @param CommentService $commentService
     */
    public function __construct(
        ActivityService $activityService,
        CommentService $commentService
    )
    {
        $this->activityService = $activityService;
        $this->commentService = $commentService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            WatchlistEvents::WATCHLIST_CREATED => "onWatchlistCreated",
            CommentEvents::COMMENT_CREATED => "onCommentCreated",
            UserEvents::USER_CONFIRMED => "onUserConfirmed"
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

    /**
     * @param CommentEvent $commentEvent
     */
    public function onCommentCreated(CommentEvent $commentEvent)
    {
        $comment = $commentEvent->getComment();
        $this->activityService->addCommentActivity($comment);
    }

    /**
     * @param UserEvent $userEvent
     */
    public function onUserConfirmed(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();
        $this->activityService->addJoinedActivity($user);
    }
}