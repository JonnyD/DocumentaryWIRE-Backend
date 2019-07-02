<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Enum\UserStatus;
use App\Service\ActivityService;
use App\Service\CommentService;
use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @var UserService
     */
    private $userService;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param ActivityService $activityService
     * @param CommentService $commentService
     * @param UserService $userService
     * @param \Swift_Mailer $mailer
     * @param UrlGeneratorInterface $router
     */
    public function __construct(
        ActivityService $activityService,
        CommentService $commentService,
        UserService $userService,
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router)
    {
        $this->activityService = $activityService;
        $this->commentService = $commentService;
        $this->userService = $userService;
        $this->mailer = $mailer;
        $this->router = $router;
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
        $this->activityService->mapActivityToUser($user);

        $activationKey = $this->userService->generateActivationCode($user);

        $url = "api/user/activate"; //@TODO

            /*$this->router->generate('activate', [
            "username" => $user->getUsername(),
            "key" => $activationKey], true);*/

        $message = (new \Swift_Message('Activate your account at DocumentaryWIRE'))
            ->setFrom(['contact@documentarywire.com' => 'DocumentaryWIRE'])
            ->setTo($user->getEmail())
            ->setBody("Thanks for registering at DocumentaryWIRE!
Please activate your account by clicking on the following link: " . $url);

        $this->mailer->send($message);
    }
}