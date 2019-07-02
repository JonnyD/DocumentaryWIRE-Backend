<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Service\ActivityService;
use App\Service\CommentService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class UserJoinedSubscriber implements EventSubscriberInterface
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
        CommentService $commentService)
    {
        $this->activityService = $activityService;
        $this->commentService = $commentService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['addUserJoinedActivity', EventPriorities::POST_WRITE],
        ];
    }

    public function addUserJoinedActivity(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User || Request::METHOD_POST !== $method) {
            return;
        }

        $this->activityService->addJoinedActivity($user);
        $this->commentService->mapCommentsToUser($user);
    }
}