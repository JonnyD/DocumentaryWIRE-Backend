<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    public function activate($data, Request $request, UserService $userService)
    {
        $userId = $data->getId();
        $activationKey = $request->query->get('activation_key');

        $userInDatabase = $userService->getUserById($userId);

        if ($activationKey === $userInDatabase->getActivationKey()) {
            $userInDatabase->setActivatedAt(new \DateTime());
            $userService->save($userInDatabase);
        }

        return $userInDatabase;
    }
}