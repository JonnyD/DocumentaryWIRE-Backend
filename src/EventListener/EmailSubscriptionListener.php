<?php

namespace App\EventListener;

use App\Event\CommentEvent;
use App\Event\CommentEvents;
use App\Event\UserEvent;
use App\Event\UserEvents;
use App\Service\DocumentaryService;
use App\Service\EmailService;
use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailSubscriptionListener implements EventSubscriberInterface
{
    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @param EmailService $emailService
     */
    public function __construct(
        EmailService $emailService
    )
    {
        $this->emailService = $emailService;
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
     * @throws \Doctrine\ORM\ORMException
     */
    public function onUserConfirmed(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();
        $email = $user->getEmail();

        $this->emailService->subscribe($email);
    }
}