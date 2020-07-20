<?php

namespace App\EventListener;

use App\Event\CommentEvent;
use App\Event\CommentEvents;
use App\Event\FollowEvent;
use App\Event\FollowEvents;
use App\Event\UserEvent;
use App\Event\UserEvents;
use App\Service\DocumentaryService;
use App\Service\EmailService;
use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateFollowerAndFollowingCountListener implements EventSubscriberInterface
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param UserService $userService
     */
    public function __construct(
        UserService $userService
    )
    {
        $this->userService = $userService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FollowEvents::FOLLOW_SAVED => "onFollowSaved",
            FollowEvents::FOLLOW_DELETED => "onFollowDeleted"
        );
    }

    /**
     * @param FollowEvent $followEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onFollowSaved(FollowEvent $followEvent)
    {
        $follow = $followEvent->getFollow();

        $userFrom = $follow->getUserFrom();
        $userTo = $follow->getUserTo();

        $this->userService->updateFollowerCountForUser($userTo);
        $this->userService->updateFollowingCountForUser($userFrom);
    }

    /**
     * @param FollowEvent $followEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onFollowDeleted(FollowEvent $followEvent)
    {
        $follow = $followEvent->getFollow();

        $userFrom = $follow->getUserFrom();
        $userTo = $follow->getUserTo();

        $userFrom->removeFollowTo($follow);
        $userTo->removeFollowFrom($follow);

        $this->userService->updateFollowerCountForUser($userTo);
        $this->userService->updateFollowingCountForUser($userFrom);
    }
}