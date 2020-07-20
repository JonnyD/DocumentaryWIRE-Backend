<?php

namespace App\EventListener;

use App\Event\CommentEvent;
use App\Event\CommentEvents;
use App\Service\ActivityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommentActivityListener implements EventSubscriberInterface
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
            CommentEvents::COMMENT_SAVED => "onCommentSaved"
        );
    }

    /**
     * @param CommentEvent $commentEvent
     */
    public function onCommentSaved(CommentEvent $commentEvent)
    {
        $comment = $commentEvent->getComment();
        $this->activityService->addCommentActivity($comment);
    }
}