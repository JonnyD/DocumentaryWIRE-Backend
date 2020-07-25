<?php

namespace App\EventListener;

use App\Event\UserEvent;
use App\Event\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendForgotUsernameEmailListener implements EventSubscriberInterface
{

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_FORGOT_USERNAME => "onForgotUsername"
        );
    }

    /**
     * @param UserEvent $userEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onForgotUsername(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();

        //@TODO send forgot username email

        /**@TODO
        $email = (new \Swift_Message('Hello Email'))
        ->setFrom(array('contact@documentarywire.com' => 'DocumentaryWIRE'))
        ->setTo(array('facebook@jonnydevine.com' => 'Test'))
        ->setSubject('Time for Symfony Mailer!')#
        ->setBody('<p>Someone requested that the password be reset for the following account: " . $user->getUsername() . ".
        If this was a mistake, just ignore this email and nothing will happen.
        To reset your password, visit the following address: " . $url</p>');

        $this->mailer->send($email);
         * **/

    }
}