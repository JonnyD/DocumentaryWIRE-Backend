<?php

namespace App\EventListener;

use App\Event\UserEvent;
use App\Event\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendChangePasswordListener implements EventSubscriberInterface
{

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_CHANGE_PASSWORD => "onChangePassword"
        );
    }

    /**
     * @param UserEvent $userEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onChangePassword(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();

        //@TODO send change password email
    }
}