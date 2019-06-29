<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    /**
     * @param TokenStorageInterface $tokenStorage
     * @return User|string
     */
    public function me(TokenStorageInterface $tokenStorage)
    {
        return $tokenStorage->getToken()->getUser();
    }
}