<?php

namespace App\EventListener;

use App\Event\UserEvent;
use App\Event\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResendConfirmationKeyListener implements EventSubscriberInterface
{

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_RESEND_CONFIRMATION_KEY => "onResendConfirmationKey"
        );
    }

    /**
     * @param UserEvent $userEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onResendConfirmationKey(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();

        //@TODO send resend confirmation key email
    }
}