<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterForm;
use App\Service\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class UserController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param UserService $userService
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserService $userService)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userService = $userService;
    }

    /**
     * @FOSRest\Post("/user/register")
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function postRegisterAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(RegisterForm::class, $user);

        $form->submit($request->request->all());

        //return new JsonResponse((string) $form->getErrors(true, false));

        if ($form->isValid()){
            $this->userService->save($user);
        }

        //@TODO send activation code email
        return new JsonResponse($user);
    }

    /**
     * @FOSRest\Get("/user/me")
     *
     * @return User|string
     */
    public function getMeAction()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $loggedInUser */
        $loggedInUser = $this->tokenStorage->getToken()->getUser();

        $data = [
            'username' => $loggedInUser->getUsername(),
            'first_name' => $loggedInUser->getFirstName(),
            'last_name' => $loggedInUser->getLastName(),
            'avatar' => $loggedInUser->getAvatar(),
            'last_login' => $loggedInUser->getLastLogin(),
            'activated_at' => $loggedInUser->getActivatedAt(),
            'enabled' => $loggedInUser->isEnabled(),
            'roles' => $loggedInUser->getRoles()
        ];

        return new JsonResponse($data, 200);
    }

    /**
     * @FOSRest\Get("/user/activate")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getActivateAction(Request $request)
    {
        /** @var User $data */
        $username = $request->query->et('username');
        $confirmationToken = $request->query->get('confirmation_token');

        $userInDatabase = $this->userService->getUserByUsername($username);
        if ($userInDatabase === null) {
            //@TODO
        }

        if ($confirmationToken === $userInDatabase->getConfirmationToken()) {
            $userInDatabase->setActivatedAt(new \DateTime());
            $userInDatabase->setEnabled(true);
            $this->userService->save($userInDatabase);

            $data = [
                'username' => $userInDatabase->getUsername(),
                'first_name' => $userInDatabase->getFirstName(),
                'last_name' => $userInDatabase->getLastName(),
                'avatar' => $userInDatabase->getAvatar(),
                'last_login' => $userInDatabase->getLastLogin(),
                'activated_at' => $userInDatabase->getActivatedAt(),
                'enabled' => $userInDatabase->isEnabled()
            ];

            return new JsonResponse($data, 200);
        }

        return new JsonResponse('TODO', 200);
    }

    /**
     * @FOSRest\Post("/user/forgot-password", name="post_user_forgot-password", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function postForgotpasswordAction(Request $request)
    {
        $username = $request->request->get('username');

        $this->userService->generatePasswordResetKey($username);

        return new JsonResponse("sent", 200);
        //@TODO Create Event and send email
    }

    /**
     * @FOSRest\Post("/user/reset-password", name="post_user_reset-password", options={ "method_prefix" = false })
     *
     * @param $data
     * @param Request $request
     * @param UserService $userService
     */
    public function postResetpasswordAction(Request $request)
    {
        $resetKey = $request->query->get('reset_key');
        if ($resetKey === null) {
            //@TODO
        }

        $username = $request->query->get('username');
        if ($username === null) {
            //@TODO
        }

        $userFromDatabase = $this->userService->getUserByUsername($username);
        if ($userFromDatabase === null) {
            //@TODO
        }

        if ($resetKey !== $userFromDatabase->getResetKey()) {
            //@TODO
        }

        $now = new \DateTime();
        $isGreaterThan24Hours = $userFromDatabase->getPasswordRequestedAt()
                ->diff($now)->format('H') > 24;

        if ($isGreaterThan24Hours) {
            //@TODO
        }

        $newPassword = $request->request->get('password');
        $userFromDatabase->setPlainPassword($newPassword);
        $userFromDatabase->setPassword($newPassword);

        $this->userService->resetPassword($userFromDatabase);
    }

    public function forgotUsername(Request $request)
    {
        $email = $request->request->get('email');
        if ($email === null) {
            //@TODO
        }

        $user = $this->userService->getUserByEmail($email);
        if ($user === null) {
            //@TODO
        }

        //@TODO Send email with $user->getUsername();
    }
}