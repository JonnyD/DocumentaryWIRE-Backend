<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /**
     * @param $data
     * @param Request $request
     * @param UserService $userService
     * @return User|null
     * @throws \Doctrine\ORM\ORMException
     */
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

    /**
     * @param Request $request
     * @param UserService $userService
     * @param TokenStorageInterface $tokenStorage
     * @throws \Doctrine\ORM\ORMException
     */
    public function forgotPasswordRequest(Request $request, UserService $userService, TokenStorageInterface $tokenStorage)
    {
        $email = $request->query->get('email');

        $userService->generatePasswordResetKey($email);

        return new JsonResponse("sent", 200);
        //@TODO Create Event and send email
    }

    /**
     * @param $data
     * @param Request $request
     * @param UserService $userService
     */
    public function resetPassword(Request $request, UserService $userService)
    {
        $resetKey = $request->query->get('reset_key');
        if ($resetKey === null) {
            //@TODO
        }

        $userId = $request->query->get('user_id');
        if ($userId === null) {
            //@TODO
        }

        $userFromDatabase = $userService->getUserById($userId);
        if ($userFromDatabase === null) {
            //@TODO
        }

        if ($resetKey !== $userFromDatabase->getResetKey()) {
            //@TODO
        }

        $now = new \DateTime();
        $isGreaterThan24Hours = $userFromDatabase->getResetRequestAt()
                ->diff($now)->format('H') > 24;

        if ($isGreaterThan24Hours) {
            //@TODO
        }

        $newPassword = $request->query->get('password');
        $userFromDatabase->setPassword($newPassword);

        $userService->resetPassword($userFromDatabase);
    }
}