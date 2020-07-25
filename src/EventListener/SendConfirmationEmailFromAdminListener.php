<?php

namespace App\EventListener;

use App\Event\UserEvent;
use App\Event\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendConfirmationEmailFromAdminListener implements EventSubscriberInterface
{

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_CREATED_BY_ADMIN => "onUserCreatedByAdmin"
        );
    }

    /**
     * @param UserEvent $userEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onUserCreatedByAdmin(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();
        $email = $user->getEmail();
        $plainPassword = $user->getPlainPassword();
        //@TODO send confirmation email
    }
}