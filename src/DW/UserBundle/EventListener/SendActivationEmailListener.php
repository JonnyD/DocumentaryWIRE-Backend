<?php

namespace DW\UserBundle\EventListener;

use DW\UserBundle\Event\UserEvent;
use DW\UserBundle\Event\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Router;

class SendActivationEmailListener implements EventSubscriberInterface
{
    /**
     * @var Router
     */
    private $router;
    private $mailer;

    /**
     * @param Router $router
     * @param $mailer
     */
    public function __construct(Router $router, $mailer)
    {
        $this->router = $router;
        $this->mailer = $mailer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvents::JOINED => "sendActivationEmail"
        ];
    }

    /**
     * @param UserEvent $userEvent
     */
    public function sendActivationEmail(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();

        $url = $this->router->generate('dw.activate', [
            "username" => $user->getUsername(),
            "key" => $user->getActivationKey()], true);

        $message = \Swift_Message::newInstance()
            ->setSubject('Activate your account at DocumentaryWIRE')
            ->setFrom(['contact@documentarywire.com' => 'DocumentaryWIRE'])
            ->setTo($user->getEmail())
            ->setBody("Thanks for registering at DocumentaryWIRE!
Please activate your account by clicking on the following link: " . $url);
        $this->mailer->send($message);
    }
}