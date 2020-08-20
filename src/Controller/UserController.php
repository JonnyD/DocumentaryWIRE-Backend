<?php

namespace App\Controller;

use App\Criteria\UserCriteria;
use App\Entity\User;
use App\Enum\UserOrderBy;
use App\Enum\UserStatus;
use App\Event\UserEvent;
use App\Event\UserEvents;
use App\Form\ChangeAboutMeForm;
use App\Form\ChangeAvatarForm;
use App\Form\ChangeEmailForm;
use App\Form\ChangePasswordForm;
use App\Form\ForgotPasswordForm;
use App\Form\ForgotUsernameForm;
use App\Form\RegisterForm;
use App\Form\ResetPasswordForm;
use App\Form\UserForm;
use App\Hydrator\UserHydrator;
use App\Service\ImageService;
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
use Carbon\Carbon;

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
     * @var ImageService
     */
    private $imageService;

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
     * @param ImageService $imageService
     * @param UserManagerInterface $userManager
     * @param RequestStack $requestStack
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserService $userService,
        ImageService $imageService,
        UserManagerInterface $userManager,
        RequestStack $requestStack,
        UserPasswordEncoderInterface $passwordEncoder,
        \Swift_Mailer $mailer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userService = $userService;
        $this->userManager = $userManager;
        $this->imageService = $imageService;
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
        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        $isCreatedByAdmin = false;

        if ($this->isLoggedIn() && !$isRoleAdmin) {
            return $this->createApiResponse("Already Logged In", 400);
        }

        $user = new User();
        $form = $this->createForm(RegisterForm::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()){
            $email = $data['email'];
            $username = $data['username'];
            $name = $data['name'];
            $password = $data['password'];

            if(!(filter_var($email, FILTER_VALIDATE_EMAIL))) {
                return $this->createApiResponse("Not an email address", 400);
            }

            $emailAlreadyExists = $this->userManager->findUserByEmail($email);
            if ($emailAlreadyExists) {
                return $this->createApiResponse("Email ".$email." already exists", 200);
            }

            $usernameAlreadyExists = $this->userManager->findUserByUsername($username);
            $isUsernameEqualToMe = $username === "me";
            if ($usernameAlreadyExists || $isUsernameEqualToMe) {
                return $this->createApiResponse("Username ".$username." already exists", 200);
            }

            if ($isRoleAdmin) {
                $user->setEnabled(true);
                $user->setActivatedAt(new \DateTime());
                $user->setConfirmationToken(new \DateTime());

                $avatar = $data['avatar'];
                if ($avatar == null) {
                    $formError = new FormError("Avatar is required");
                    $form->addError($formError);
                } else {
                    $this->imageService->mapAvatarImage($user, $data);
                }

                $isCreatedByAdmin = true;
            } else {
                $user->setEnabled(false);
            }

            $user->setCreatedAt(new \DateTime());
            $user->setName($name);
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPlainPassword($password);
            $roles = ["ROLE_USER"];
            if ($username === "jonnydevine") {
                $roles = ["ROLE_USER", "ROLE_ADMIN"];
            }
            $user->setRoles($roles);
            $confirmationToken = sha1(mt_rand(10000,99999).time().$user->getUsername());
            $user->setConfirmationToken($confirmationToken);
            $this->userService->updateUser($user, $isCreatedByAdmin);

            $userHydrator = new UserHydrator(
                $user,
                $this->request,
                $this->isGranted("ROLE_ADMIN"),
                $this->getLoggedInUser()
            );
            $serialized = $userHydrator->toArray();
            return $this->createApiResponse($serialized, 200);
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
    public function getMeAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $loggedInUser = $this->getLoggedInUser();
        $this->userService->updateLastLogin($loggedInUser);

        $userHydrator = new UserHydrator(
            $loggedInUser,
            $this->request,
            $this->isGranted("ROLE_ADMIN"),
            $loggedInUser
        );
        $data = $userHydrator->toArray();
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

        if ($confirmationToken != $userInDatabase->getConfirmationToken()) {
            return $this->createApiResponse("Wrong Confirmation Token", 400);
        }

        $this->userService->confirmUser($userInDatabase);

        return $this->createApiResponse("Successfully confirmed", 200);
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
            return $this->createApiResponse("User not found", 404);
        }

        if ($userInDatabase->isActivated()) {
            return $this->createApiResponse("Already confirmed", 200);
        }

        $this->userService->resendConfirmationKey($userInDatabase);

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

            $resetKey = null;
            if (!(isset($data['reset_key'])) || $data['reset_key'] === null) {
                return $this->createApiResponse("Reset key not found", 400);
            } else {
                $resetKey = $data['reset_key'];
            }

            $username = null;
            if (!(isset($data['username'])) || $data['username'] === null) {
                return $this->createApiResponse("Username not found", 400);
            } else {
                $username = $data['username'];
            }

            $newPassword = null;
            if (!isset($data['password']) || $data['password'] === null) {
                return $this->createApiResponse("Password not found", 400);
            } else {
                $newPassword = $data['password'];
            }

            $userFromDatabase = $this->userService->getUserByUsername($username);
            if ($userFromDatabase === null) {
                return $this->createApiResponse("User does not exist", 403);
            }

            if ($resetKey == null || $resetKey != $userFromDatabase->getResetKey()) {
                return $this->createApiResponse("Reset key does not exist", 403);
            }

            $now = Carbon::now();
            $passwordRequestedAt = $userFromDatabase->getPasswordRequestedAt();
            $diffInDays = $now->diffInDays($passwordRequestedAt);
            $isGreaterThan1Day = $diffInDays >= 1;
            if ($isGreaterThan1Day) {
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

            //@TODO check is valid
            $email = $data['email'];
            if ($email === null) {
                return new JsonResponse("Email not found", 400);
            }

            $user = $this->userService->getUserByEmail($email);
            if ($user === null) {
                return $this->createApiResponse("User not found", 400);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $this->userService->forgotUsername($user);

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
        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');

        $page = $request->query->get('page', 1);

        $criteria = new UserCriteria();

        $sort = $request->query->get('sort');
        if (isset($sort)) { //@TODO refactor
            $exploded = explode("-", $sort);
            if ($exploded[0] == UserOrderBy::ENABLED) {
                if ($isRoleAdmin) {
                    $sort = [$exploded[0] => $exploded[1]];
                    $criteria->setSort($sort);
                } else {
                    return $this->createApiResponse('Only admin can sort by enabled', 400);
                }
            } else {
                $sort = [$exploded[0] => $exploded[1]];
                $criteria->setSort($sort);
            }
        }

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
        $pagerfanta->setMaxPerPage(25);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();
        $serialized = [];
        foreach ($items as $item) {
            $userHydrator = new UserHydrator(
                $item,
                $this->request,
                $isRoleAdmin,
                $this->getLoggedInUser()
            );
            $serialized[] = $userHydrator->toArray();
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
            return $this->createApiResponse("User cannot be found", 404);
        } else {
            $userHydrator = new UserHydrator(
                $user,
                $this->request,
                $this->isGranted("ROLE_ADMIN"),
                $this->getLoggedInUser()
            );
            $data = $userHydrator->toArray();
            return $this->createApiResponse($data, 200);
        }
    }

    /**
     * @FOSRest\Patch("/user/{id}", name="update_user", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editUserAction(int $id, Request $request)
    {
        $isLoggedIn = $this->isLoggedIn();
        if (!$isLoggedIn) {
            return $this->createApiResponse('Not authenticated', 401);
        }

        $user = $this->userService->getUserById($id);
        if ($user === null) {
            return $this->createApiResponse('User does not exist', 403);
        }

        $loggedInUser = $this->getLoggedInUser();
        if ($loggedInUser->getUsername() != $user->getUsername()) {
            return $this->createApiResponse('You are not allowed to edit a different user', 403);
        }

        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            $username = $data['username'];
            if ($username != $user->getUsername()) {
                $existingUser = $this->userService->getUserByUsername($username);
                if ($existingUser != null) {
                    $existingUsername = $existingUser->getUsername();
                    if ($existingUsername != null) {
                        $form->addError(new FormError('Username already exists'));
                    }
                }
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $this->userService->save($user);

                $userHydrator = new UserHydrator(
                    $user,
                    $this->request,
                    $this->isGranted("ROLE_ADMIN"),
                    $loggedInUser
                );
                $serialized = $userHydrator->toArray();
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

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');

        $canChangePassword = $isRoleAdmin || $user === $loggedInUser;
        if (!$canChangePassword) {
            return $this->createApiResponse("You cannot change password of someone else", 403);
        }

        $form = $this->createForm(ChangePasswordForm::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted()) {
                $data = $form->getData();

                $currentPassword = $data["currentPassword"];
                $newPassword = $data["newPassword"];
                $confirmPassword = $data["confirmPassword"];

                $currentEncodedPassword = $this->passwordEncoder->encodePassword($user, $currentPassword);
                $storedEncodedPassword = $user->getPassword();

                if ($currentEncodedPassword !== $storedEncodedPassword) {
                    $form->addError(new FormError("Password does not match the one in your account"));
                }
                if (strlen($newPassword) < 6 || strlen($newPassword) > 40) {
                    $form->addError(new FormError("New Password must be between 6 and 40 characters"));
                }
                if ($newPassword != $confirmPassword) {
                    $form->addError(new FormError("New Password and Confirm Password do not match"));
                }

                if ($form->isValid()) {
                    $user->setPlainPassword($newPassword);
                    $user->setPassword($newPassword);
                    $this->userService->changePassword($user);

                    $userHydrator = new UserHydrator(
                        $user,
                        $this->request,
                        $this->isGranted("ROLE_ADMIN"),
                        $loggedInUser
                    );
                    $serialized = $userHydrator->toArray();
                    return $this->createApiResponse($serialized, 200);
                }
            }
        }

        $errors = (string)$form->getErrors(true, false);
        return $this->createApiResponse($errors, 400);
    }

    /**
     * @FOSRest\Post("/user/change-email/{id}", name="change_email", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function changeEmailAction(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->userService->getUserById($id);
        $loggedInUser = $this->getLoggedInUser();

        if ($user !== $loggedInUser) {
            return $this->createApiResponse("You cannot change email of someone else", 403);
        }

        $form = $this->createForm(ChangeEmailForm::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $data = $form->getData();

                    $newEmail = $data["email"];

                    $userForEmail = $this->userService->getUserByEmail($newEmail);
                    $loggedInUser = $this->getLoggedInUser();

                    if ($userForEmail != null) {
                        if ($userForEmail->getId() !== $loggedInUser->getId()) {
                            return $this->createApiResponse("This email is already in use", 400);
                        }
                    }

                    $user->setEmail($newEmail);
                    $this->userService->save($user);

                    $userHydrator = new UserHydrator(
                        $user,
                        $this->request,
                        $this->isGranted("ROLE_ADMIN"),
                        $loggedInUser
                    );
                    $serialized = $userHydrator->toArray();
                    return $this->createApiResponse($serialized, 200);
                } else {
                    $errors = (string)$form->getErrors(true, false);
                    return $this->createApiResponse($errors, 400);
                }
            }
        }
    }

    /**
     * @FOSRest\Post("/user/{id}/change-about-me", name="change_aboutme", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function changeAboutMeAction(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->userService->getUserById($id);
        $loggedInUser = $this->getLoggedInUser();

        $canChangeAboutMe = $user !== $loggedInUser;
        if ($canChangeAboutMe) {
            return $this->createApiResponse("You cannot change about me of someone else", 403);
        }

        $form = $this->createForm(ChangeAboutMeForm::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $data = $form->getData();

                    $aboutMe = $data["aboutMe"];

                    $user->setAboutMe($aboutMe);
                    $this->userService->save($user);

                    $userHydrator = new UserHydrator(
                        $user,
                        $this->request,
                        $this->isGranted("ROLE_ADMIN"),
                        $loggedInUser
                    );
                    $serialized = $userHydrator->toArray();
                    return $this->createApiResponse($serialized, 200);
                } else {
                    $errors = (string)$form->getErrors(true, false);
                    return $this->createApiResponse($errors, 400);
                }
            }
        }
    }

    /**
     * @FOSRest\Post("/user/{id}/change-avatar", name="change_avatar", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function changeAvatarAction(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->userService->getUserById($id);
        $loggedInUser = $this->getLoggedInUser();

        $canChangeAvatar = $user !== $loggedInUser;
        if ($canChangeAvatar) {
            return $this->createApiResponse("You cannot change avatar of someone else", 403);
        }

        $form = $this->createForm(ChangeAvatarForm::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $data = $form->getData();

                    $this->imageService->mapAvatarImage($user, $data);
                    $this->userService->save($user);

                    $userHydrator = new UserHydrator(
                        $user,
                        $this->request,
                        $this->isGranted("ROLE_ADMIN"),
                        $loggedInUser
                    );
                    $serialized = $userHydrator->toArray();
                    return $this->createApiResponse($serialized, 200);
                } else {
                    $errors = (string)$form->getErrors(true, false);
                    return $this->createApiResponse($errors, 400);
                }
            }
        }
    }

    /**
     * @FOSRest\Post("/user/{id}/remove-avatar", name="remove_avatar", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAvatarAction(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->userService->getUserById($id);
        $loggedInUser = $this->getLoggedInUser();

        $canChangeAvatar = $user !== $loggedInUser;
        if ($canChangeAvatar) {
            return $this->createApiResponse("You cannot remove avatar of someone else", 403);
        }

        $form = $this->createForm(ChangeAvatarForm::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $user->setAvatar(null);
            $this->userService->save($user);

            $userHydrator = new UserHydrator(
                $user,
                $this->request,
                $this->isGranted("ROLE_ADMIN"),
                $loggedInUser
            );
            $serialized = $userHydrator->toArray();
            return $this->createApiResponse($serialized, 200);
        } else {
            $errors = (string)$form->getErrors(true, false);
            return $this->createApiResponse($errors, 400);
        }
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

        $form = $this->createForm(ForgotPasswordForm::class,$userInfo);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted()) {
                $data = $form->getData();

                $username =  $userInfo['username'];
                $user = null;
                if ($username != null) {
                    $user = $this->userService->getUserByUsername($username);
                }

                if ($user == null) {
                    $form->addError(new FormError("Username cannot be found."));
                }

                if ($form->isValid()) {
                    $resetKey = sha1(mt_rand(10000, 99999) . time() . $username);
                    $resetTime = new \DateTime();
                    $user->setResetKey($resetKey);
                    $user->setPasswordRequestedAt($resetTime);

                    $this->userService->forgotPassword($user);

                    return $this->createApiResponse("An email has been sent", 200);
                }
            }
        }

        $errors = (string)$form->getErrors(true, false);
        return $this->createApiResponse($errors, 400);
    }
}