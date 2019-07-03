<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        /** @var User $data */
        $userId = $data->getId();
        $activationKey = $request->query->get('activation_key');

        $userInDatabase = $userService->getUserById($userId);

        if ($activationKey === $userInDatabase->getActivationKey()) {
            $userInDatabase->setActivatedAt(new \DateTime());
            $userService->save($userInDatabase);
        }

        return $userInDatabase;
    }

    public function resetPasswordRequest($data, UserService $userService, TokenStorageInterface $tokenStorage)
    {
        /** @var User $userFromRequest */
        $userFromRequest = $data;
        /** @var User $loggedInUser */
        $loggedInUser = $tokenStorage->getToken()->getUser();

        if ($userFromRequest->getId() !== $loggedInUser->getId()) {
            throw new AccessDeniedException();
        }

        $userService->generatePasswordResetKey($data);

        //@TODO Create Event
    }

    public function resetPassword($data, Request $request, UserService $userService, TokenStorageInterface $tokenStorage)
    {
        /** @var User $userFromRequest */
        $userFromRequest = $data;

        $resetKey = $request->query->get('reset_key');
        if ($resetKey === null) {
            throw new AccessDeniedException();
        }

        $userFromDatabase = $userService->getUserById($userFromRequest->getId());
        if ($userFromDatabase === null) {
            throw new AccessDeniedException();
        }

        $now = new \DateTime();
        $isGreaterThan24Hours = $userFromDatabase->getResetRequestAt()
                ->diff($now)->format('H') > 24;

        //return $isGreaterThan24Hours;

        if ($isGreaterThan24Hours) {
            throw new AccessDeniedException();
        }

        // set user pssword

        $userService->resetPassword($userFromDatabase);

        return $userFromDatabase;
    }
}