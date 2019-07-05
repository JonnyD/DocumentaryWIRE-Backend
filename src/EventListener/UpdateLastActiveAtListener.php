<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Entity\User;

class UpdateLastActiveAtListener
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * LoginListener constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();

        $user->setLastActiveAt(new \DateTime());

        $this->em->persist($user);
        $this->em->flush();
    }
}