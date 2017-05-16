<?php

namespace DW\UserBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use DW\BaseBundle\Enum\Order;
use DW\UserBundle\Criteria\UserCriteria;
use DW\UserBundle\Entity\User;
use DW\UserBundle\Enum\OrderBy;
use DW\UserBundle\Event\UserEvent;
use DW\UserBundle\Event\UserEvents;
use DW\UserBundle\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class UserService
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param UserRepository $userRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        UserRepository $userRepository,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $limit
     * @return ArrayCollection|User[]
     */
    public function getLatestMembers(int $limit) : array
    {
        $criteria = new UserCriteria();
        $criteria->setIsActivated(true);
        $criteria->setSort([
            OrderBy::ACTIVATED_AT => Order::DESC
        ]);
        $criteria->setLimit($limit);

        return $this->userRepository->findAllByCriteria($criteria);
    }

    /**
     * @param int $limit
     * @return ArrayCollection|User[]
     */
    public function getRecentlyActiveMembers(int $limit) : array
    {
        $criteria = new UserCriteria();
        $criteria->setIsActivated(true);
        $criteria->setSort([
            OrderBy::LAST_ACTIVE_AT => Order::DESC
        ]);
        $criteria->setLimit($limit);

        return $this->userRepository->findAllByCriteria($criteria);
    }

    /**
     * @param string $username
     * @return User
     */
    public function getUserByUsername(string $username)
    {
        return $this->userRepository->findOneByUsername($username);
    }

    /**
     * @param string $email
     * @return User
     */
    public function getUserByEmail(string $email)
    {
        return $this->userRepository->findOneByEmail($email);
    }

    /**
     * @param User $user
     */
    public function registerUser(User $user)
    {
        $this->userRepository->save($user);
        $this->eventDispatcher->dispatch(UserEvents::JOINED, new UserEvent($user));
    }

    /**
     * @param User $user
     */
    public function confirmUser(User $user)
    {
        $user->setLastActiveAt(new \DateTime());
        $this->userRepository->save($user);
        $this->eventDispatcher->dispatch(UserEvents::CONFIRMED, new UserEvent($user));
    }

    /**
     * @param User $user
     */
    public function loginUser(User $user)
    {
        $user->setLastActiveAt(new \DateTime());
        $this->userRepository->save($user);
        $this->eventDispatcher->dispatch(UserEvents::LOGIN, new UserEvent($user));
    }

    /**
     * @param $email
     * @param int $s
     * @param string $d
     * @param string $r
     * @param bool $img
     * @param array $atts
     * @return string
     */
    public function getGravatar( $email, $s = 80, $d = 'wavatar', $r = 'g', $img = false, $atts = [])
    {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&d=$d&r=$r";
        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }

    /**
     * @param User $user
     * @param bool $sync
     */
    public function save(User $user, bool $sync = true)
    {
        $this->userRepository->save($user);
    }
}