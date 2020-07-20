<?php

namespace App\EventListener;

use App\Event\UserEvent;
use App\Event\UserEvents;
use App\Service\ActivityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JoinedActivityListener implements EventSubscriberInterface
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
            UserEvents::USER_CONFIRMED => "onUserConfirmed"
        );
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