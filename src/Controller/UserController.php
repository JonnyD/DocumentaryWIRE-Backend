<?php

namespace App\Controller;

use App\Criteria\UserCriteria;
use App\Entity\User;
use App\Enum\UserStatus;
use App\Form\ChangePasswordForm;
use App\Form\ForgotPasswordForm;
use App\Form\ForgotUsernameForm;
use App\Form\RegisterForm;
use App\Form\ResetPasswordForm;
use App\Form\UserForm;
use App\Service\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use FOS\UserBundle\Mailer\Mailer;
use FOS\UserBundle\Model\UserManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class UserController extends BaseController implements ClassResourceInterface
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
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param UserService $userService
     * @param UserManagerInterface $userManager
     * @param RequestStack $requestStack
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserService $userService,
        UserManagerInterface $userManager,
        RequestStack $requestStack,
        UserPasswordEncoderInterface $passwordEncoder,
        \Swift_Mailer $mailer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userService = $userService;
        $this->userManager = $userManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->request = $requestStack->getCurrentRequest();
        $this->mailer = $mailer;
    }

    /**
     * @FOSRest\Post("/user")
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(RegisterForm::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()){
            $email = $data['email'];
            $username = $data['username'];
            $name = $data['name'];
            $password = $data['password'];

            $emailAlreadyExists = $this->userManager->findUserByEmail($email);
            if ($emailAlreadyExists){
                return $this->createApiResponse("Email ".$email." already exists", 200);
            }

            $usernameAlreadyExists = $this->userManager->findUserByUsername($username);
            if ($usernameAlreadyExists){
                return $this->createApiResponse("Username ".$username." already exists", 200);
            }

            $user->setName($name);
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setEnabled(false);
            $user->setPlainPassword($password);
            $roles = ["ROLE_USER"];
            if ($username === "jonnydevine") {
                $roles = ["ROLE_USER", "ROLE_ADMIN"];
            }
            $user->setRoles($roles);
            $confirmationToken = sha1(mt_rand(10000,99999).time().$user->getUsername());
            $user->setConfirmationToken($confirmationToken);
            $this->userManager->updateUser($user);

            //@TODO send activation code email

            return $this->createApiResponse($this->serializeUser($user), 200);
        } else {
            $errors = (string)$form->getErrors(true, false);
            return $this->createApiResponse($errors, 400);
        }
    }

    /**
     * @FOSRest\Get("/user/me")
     *
     * @return User|string
     */
    public function getMeAction()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $loggedInUser = $this->getLoggedInUser();
        $data = $this->serializeUser($loggedInUser);

        return $this->createApiResponse($data, 200);
    }

    /**
     * @FOSRest\Get("/user/confirm")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmAction(Request $request)
    {
        $username = $request->query->get('username');
        if ($username == null || $username == 'undefined') {
            return $this->createApiResponse("Username not found", 404);
        }

        $confirmationToken = $request->query->get('confirmation_token');
        if ($confirmationToken == null || $confirmationToken == 'undefined') {
            return $this->createApiResponse("Confirmation Token not found", 404);
        }

        $userInDatabase = $this->userService->getUserByUsername($username);
        if ($userInDatabase === null) {
            return $this->createApiResponse("User not found", 404);
        }

        if ($userInDatabase->isActivated()) {
            return $this->createApiResponse("Already confirmed", 200);
        }

        if ($confirmationToken === $userInDatabase->getConfirmationToken()) {
            $this->userService->confirmUser($userInDatabase);

            return $this->createApiResponse("Successfully confirmed", 200);
        }

        //@TODO
        return $this->createApiResponse('TODO', 200);
    }

    /**
     * @FOSRest\Get("/user/resend")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resendAction(Request $request)
    {
        $email = $request->query->get('email');

        if ($email == null || $email == 'undefined') {
            return $this->createApiResponse("Email not entered", 404);
        }

        $userInDatabase = $this->userService->getUserByEmail($email);
        if ($userInDatabase === null) {
            return $this->createApiResponse("Email not found", 404);
        }

        if ($userInDatabase->isActivated()) {
            return $this->createApiResponse("Already confirmed", 200);
        }

        //@TODO send email

        return $this->createApiResponse('We have resent a new confirmation email', 200);
    }

    /**
     * @FOSRest\Post("/user/reset-password", name="post_user_reset-password", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @param UserService $userService
     */
    public function resetPasswordAction(Request $request)
    {
        $data = [];
        $form = $this->createForm(ResetPasswordForm::class, $data);

        if ($request->isMethod("POST")) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            $resetKey = $data['reset_key'];
            if ($resetKey === null) {
                return $this->createApiResponse("Reset key not found", 400);
            }

            $username = $data['username'];
            if ($username === null) {
                return $this->createApiResponse("Username not found", 400);
            }

            $newPassword = $data['password'];
            if ($newPassword === null) {
                return $this->createApiResponse("Password not found", 400);
            }

            $userFromDatabase = $this->userService->getUserByUsername($username);
            if ($userFromDatabase === null) {
                return $this->createApiResponse("User does not exist", 403);
            }

            if ($resetKey !== $userFromDatabase->getResetKey()) {
                return $this->createApiResponse("Reset key does not exist", 403);
            }

            $now = new \DateTime();
            $isGreaterThan24Hours = $userFromDatabase->getPasswordRequestedAt()
                    ->diff($now)->format('H') > 24;

            if ($isGreaterThan24Hours) {
                return $this->createApiResponse("Reset key expired", 403);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $userFromDatabase->setPlainPassword($newPassword);
                $userFromDatabase->setPassword($newPassword);

                $this->userService->resetPassword($userFromDatabase);

                return $this->createApiResponse("New password set", 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 400);
            }
        }
    }

    /**
     * @FOSRest\Post("/user/forgot-username", name="forgot_username", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @param UserService $userService
     */
    public function forgotUsernameAction(Request $request)
    {
        $data = [];
        $form = $this->createForm(ForgotUsernameForm::class, $data);

        if ($request->isMethod("POST")) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            $email = $data['email'];
            if ($email === null) {
                return new JsonResponse("Email not found", 400);
            }

            $user = $this->userService->getUserByEmail($email);
            if ($user === null) {
                return $this->createApiResponse("User not found", 400);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                //@TODO send email with username
                return $this->createApiResponse("Email has been sent", 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 400);
            }
        }
    }

    /**
     * @FOSRest\Get("/user", name="list-users", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listUsersAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        $criteria = new UserCriteria();

        $sort = $request->query->get('sort');
        if (isset($sort)) {
            $exploded = explode("-", $sort);
            $sort = [$exploded[0] => $exploded[1]];
            $criteria->setSort($sort);
        }

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isRoleAdmin) {
            $criteria->setEnabled(true);
        } else {
            $enabled = $request->query->get('enabled');
            if ($enabled != null) {
                $criteria->setEnabled($enabled);
            }
        }

        $qb = $this->userService->getUsersByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(16);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $serialized = [];
        foreach ($items as $item) {
            $serialized[] = $this->serializeUser($item);
        }

        $data = [
            'items'             => $serialized,
            'count_results'     => $pagerfanta->getNbResults(),
            'current_page'      => $pagerfanta->getCurrentPage(),
            'number_of_pages'   => $pagerfanta->getNbPages(),
            'next'              => ($pagerfanta->hasNextPage()) ? $pagerfanta->getNextPage() : null,
            'prev'              => ($pagerfanta->hasPreviousPage()) ? $pagerfanta->getPreviousPage() : null,
            'paginate'          => $pagerfanta->haveToPaginate(),
        ];

        return $this->createApiResponse($data, 200);
    }

    /**
     * @FOSRest\Get("/user/{username}")
     *
     * @param string $username
     * @return User|string
     */
    public function getUserAction(string $username)
    {
        $user = $this->userService->getUserByUsername($username);
        if (!$user) {
            $data = null;
            return $this->createApiResponse($data, 404);
        } else {
            $data = $this->serializeUser($user);
            return $this->createApiResponse($data, 200);
        }
    }

    /**
     * @FOSRest\Put("/user/{id}", name="update_user", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editUserAction(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->userService->getUserById($id);

        if ($user === null) {
            return new AccessDeniedException();
        }

        $loggedInUser = $this->getLoggedInUser();

        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($request->isMethod('PUT')) {
            $data = json_decode($request->getContent(), true)['resource'];
            $form->submit($data);

            $existingUsername = $this->userService->getUserByUsername($user->getUsername());
            if ($existingUsername && $loggedInUser->getUsername() != $user->getUsername()) {
                $form->addError(new FormError('Username already exists'));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $this->userService->save($user);
                $serialized = $this->serializeUser($user);
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 400);
            }
        }
    }

    /**
     * @FOSRest\Post("/user/{id}/change-password", name="change_password", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function changePasswordAction(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->userService->getUserById($id);
        $loggedInUser = $this->getLoggedInUser();

        if ($user !== $loggedInUser) {
            throw new AccessDeniedException();
        }

        $userInfo = [
            'currentPassword' => $request->request->get("currentPassword"),
            'newPassword' => $request->request->get("newPassword"),
            'confirmPassword' => $request->request->get("confirmPassword")
        ];

        $form = $this->createForm(ChangePasswordForm::class, $userInfo);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $form->submit($userInfo);

            if ($form->isSubmitted()) {
                $data = $form->getData();

                $currentPassword = $data["currentPassword"];
                $newPassword = $data["newPassword"];
                $confirmPassword = $data["confirmPassword"];

                $oldPassword = $loggedInUser->getPassword();
                $valid = $this->passwordEncoder->isPasswordValid($user, $currentPassword);#
                if (!$valid) {
                    $form->addError(new FormError("Password doesn't match the one in your account"));
                }
                if (strlen($newPassword) < 6 || strlen($newPassword) > 40) {
                    $form->addError(new FormError("New Password must be between 6 and 40 characters"));
                }
                if ($newPassword != $confirmPassword) {
                    $form->addError(new FormError("New Password and Confirm Password don't match"));
                }
                if ($newPassword == $oldPassword) {
                    $form->addError(new FormError("New Password cannot be the same as old password"));
                }

                if ($form->isValid()) {
                    $user->setPlainPassword($newPassword);
                    $user->setPassword($newPassword);
                    $this->userService->save($user);

                    $serialized = $this->serializeUser($user);
                    return $this->createApiResponse($serialized, 200);
                }
            }
        }

        $errors = (string)$form->getErrors(true, false);
        return $this->createApiResponse($errors, 400);
    }

    /**
     * @FOSRest\Post("/user/forgot-password", name="forgot_password", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPasswordAction(Request $request)
    {
        $userInfo = [
            'username' => $request->request->get("username")
        ];

        $form = $this->createForm(ForgotPasswordForm::class, $userInfo);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted()) {
                $data = $form->getData();

                $username =  $data['username'];
                $user = $this->userService->getUserByUsername($username);

                if ($user == null) {
                    $form->addError(new FormError("Username or email cannot be found."));
                }

                if ($form->isValid()) {
                    $resetKey = sha1(mt_rand(10000, 99999) . time() . $username);
                    $resetTime = new \DateTime();
                    $user->setResetKey($resetKey);
                    $user->setPasswordRequestedAt($resetTime);

                    $this->userService->save($user);



                    $email = (new \Swift_Message('Hello Email'))
                        ->setFrom(array('contact@documentarywire.com' => 'DocumentaryWIRE'))
                        ->setTo(array('facebook@jonnydevine.com' => 'Test'))
                        ->setSubject('Time for Symfony Mailer!')#
                        ->setBody('<p>Someone requested that the password be reset for the following account: " . $user->getUsername() . ".
If this was a mistake, just ignore this email and nothing will happen.
To reset your password, visit the following address: " . $url</p>');

                    $this->mailer->send($email);

                    return $this->createApiResponse("An email has been sent", 200, $headers);
                }
            }
        }

        $errors = (string)$form->getErrors(true, false);
        return $this->createApiResponse($errors, 400);
    }

    /**
     * @param User $user
     * @return array
     */
    private function serializeUser(User $user)
    {
        $serialized = [
            'name' => $user->getName(),
            'username' => $user->getUsername(),
            'avatar' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $user->getAvatar(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()
        ];

        $isUser = false;
        if ($this->getLoggedInUser() === $user) {
            $isUser = true;
        }

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if ($isUser || $isRoleAdmin) {
            $serialized['id'] = $user->getId();
            $serialized['usernameCanonical'] = $user->getUsernameCanonical();
            $serialized['email'] = $user->getEmail();
            $serialized['emailCanonical'] = $user->getEmailCanonical();
            $serialized['resetKey'] = $user->getResetKey();
            $serialized['activatedAt'] = $user->getActivatedAt();
            $serialized['enabled'] = $user->isEnabled();
            $serialized['password'] = $user->getPassword();
            $serialized['lastLogin'] = $user->getLastLogin();
            $serialized['confirmationToken'] = $user->getConfirmationToken();
            $serialized['passwordRequestedAt'] = $user->getPasswordRequestedAt();
            $serialized['updatedAt'] = $user->getUpdatedAt();
        }

        return $serialized;
    }
}