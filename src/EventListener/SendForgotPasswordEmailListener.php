<?php

namespace App\EventListener;

use App\Event\UserEvent;
use App\Event\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendForgotPasswordEmailListener implements EventSubscriberInterface
{

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_FORGOT_PASSWORD => "onForgotPassword"
        );
    }

    /**
     * @param UserEvent $userEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onForgotPassword(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();

        //@TODO send forgot password email
    }
}