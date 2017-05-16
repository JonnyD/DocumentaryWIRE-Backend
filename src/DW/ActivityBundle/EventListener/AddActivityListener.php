<?php

namespace DW\ActivityBundle\EventListener;

use DW\ActivityBundle\Service\ActivityService;
use DW\CommentBundle\Event\CommentEvent;
use DW\CommentBundle\Event\CommentEvents;
use DW\UserBundle\Event\UserEvent;
use DW\UserBundle\Event\UserEvents;
use DW\WatchlistBundle\Event\WatchlistEvent;
use DW\WatchlistBundle\Event\WatchlistEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddActivityListener implements EventSubscriberInterface
{
    private $activityService;

    public static function getSubscribedEvents()
    {
        return array(
            WatchlistEvents::WATCHLISTED => "onDocumentaryWatchlisted",
            WatchlistEvents::UNWATCHLISTED => "onDocumentaryUnwatchlisted",
            UserEvents::CONFIRMED => "onUserConfirmed",
            CommentEvents::COMMENT_ADDED => "onCommentAdded"
        );
    }

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * @param WatchlistEvent $watchlistEvent
     */
    public function onDocumentaryWatchlisted(WatchlistEvent $watchlistEvent)
    {
        $watchlist = $watchlistEvent->getWatchlist();
        $this->activityService->addWatchlistActivity($watchlist);
    }

    /**
     * @param WatchlistEvent $watchlistEvent
     */
    public function onDocumentaryUnwatchlisted(WatchlistEvent $watchlistEvent)
    {
        $watchlist = $watchlistEvent->getWatchlist();
        $this->activityService->removeWatchlistActivity($watchlist);
    }

    /**
     * @param UserEvent $userEvent
     */
    public function onUserConfirmed(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();
        $this->activityService->addJoinedActivity($user);
    }

    /**
     * @param CommentEvent $commentEvent
     */
    public function onCommentAdded(CommentEvent $commentEvent)
    {
        $comment = $commentEvent->getComment();
        $this->activityService->addCommentActivity($comment);
    }

    /**
     * @param CommentEvent $commentEvent
     */
    public function onCommentRemoved(CommentEvent $commentEvent)
    {
        $comment = $commentEvent->getComment();
        $this->activityService->removeCommentActivity($comment);
    }
}