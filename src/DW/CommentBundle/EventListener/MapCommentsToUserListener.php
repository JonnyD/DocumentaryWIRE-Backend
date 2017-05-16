<?php

namespace DW\CommentBundle\EventListener;

use DW\CommentBundle\Service\CommentService;
use DW\UserBundle\Event\UserEvent;
use DW\UserBundle\Event\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MapCommentsToUserListener implements EventSubscriberInterface
{
    /**
     * @var CommentService
     */
    private $commentService;

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::CONFIRMED => "onUserConfirmed"
        ];
    }

    /**
     * @param CommentService $commentService
     */
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * @param UserEvent $userEvent
     */
    public function onUserConfirmed(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();
        $this->commentService->mapCommentsToUser($user);
    }
}