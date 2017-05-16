<?php

namespace DW\UserBundle\Service;

use DW\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class SecurityService
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * @param TokenStorage $tokenStorage
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @return User
     */
    public function getLoggedInUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->authorizationChecker->isGranted('ROLE_ADMIN') ||
            $this->authorizationChecker->isGranted('ROLE_USER');
    }
}