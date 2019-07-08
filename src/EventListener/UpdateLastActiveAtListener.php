<?php

namespace App\EventListener;

use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Event\OAuthEvent;

class UpdateLastActiveAtListener
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function onPreAuthorizationProcess(OAuthEvent $event)
    {
        if ($user = $this->getUser($event)) {
            $event->setAuthorizedClient(
                $user->isAuthorizedClient($event->getClient())
            );
        }
    }

    public function onPostAuthorizationProcess(OAuthEvent $event)
    {
        if ($event->isAuthorizedClient()) {
            if (null !== $client = $event->getClient()) {
                $user = $this->getUser($event);
                $user->addClient($client);
                $user->save();

                $username = $event->getUser()->getUsername();
                var_dump($username); die();
                $user = $this->userService->getUserByUsername($username);
                $user->setLastLogin(new \DateTime());
                $this->userService->save($user);
            }
        }
    }

    protected function getUser(OAuthEvent $event)
    {
        return UserQuery::create()
            ->filterByUsername($event->getUser()->getUsername())
            ->findOne();
    }
}