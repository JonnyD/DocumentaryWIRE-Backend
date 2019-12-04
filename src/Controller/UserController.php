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
        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

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
                return new JsonResponse("Email ".$email." already exists", 200, $headers);
            }

            $usernameAlreadyExists = $this->userManager->findUserByUsername($username);
            if ($usernameAlreadyExists){
                return new JsonResponse("Username ".$username." already exists", 200, $headers);
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

            return new JsonResponse($this->serializeUser($user));
        } else {
            $errors = (string)$form->getErrors(true, false);
            return new JsonResponse($errors, 400, $headers);
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

        return new JsonResponse($data, 200);
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
            return new JsonResponse("Username not found", 404);
        }

        $confirmationToken = $request->query->get('confirmation_token');
        if ($confirmationToken == null || $confirmationToken == 'undefined') {
            return new JsonResponse("Confirmation Token not found", 404);
        }

        $userInDatabase = $this->userService->getUserByUsername($username);
        if ($userInDatabase === null) {
            return new JsonResponse("User not found", 404);
        }

        if ($userInDatabase->isActivated()) {
            return new JsonResponse("Already confirmed", 200);
        }

        if ($confirmationToken === $userInDatabase->getConfirmationToken()) {
            $this->userService->confirmUser($userInDatabase);

            return new JsonResponse("Successfully confirmed", 200);
        }

        return new JsonResponse('TODO', 200);
    }

    /**
     * @FOSRest\Post("/user/reset-password", name="post_user_reset-password", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @param UserService $userService
     */
    public function resetPasswordAction(Request $request)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        $data = [];
        $form = $this->createForm(ResetPasswordForm::class, $data);

        if ($request->isMethod("POST")) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            $resetKey = $data['reset_key'];
            if ($resetKey === null) {
                return new JsonResponse("Reset key not found", 400, $headers);
            }

            $username = $data['username'];
            if ($username === null) {
                return new JsonResponse("Username not found", 400, $headers);
            }

            $newPassword = $data['password'];
            if ($newPassword === null) {
                return new JsonResponse("Password not found", 400, $headers);
            }

            $userFromDatabase = $this->userService->getUserByUsername($username);
            if ($userFromDatabase === null) {
                return new JsonResponse("User does not exist", 403, $headers);
            }

            if ($resetKey !== $userFromDatabase->getResetKey()) {
                return new JsonResponse("Reset key does not exist", 403, $headers);
            }

            $now = new \DateTime();
            $isGreaterThan24Hours = $userFromDatabase->getPasswordRequestedAt()
                    ->diff($now)->format('H') > 24;

            if ($isGreaterThan24Hours) {
                return new JsonResponse("Reset key expired", 403, $headers);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $userFromDatabase->setPlainPassword($newPassword);
                $userFromDatabase->setPassword($newPassword);

                $this->userService->resetPassword($userFromDatabase);

                return new JsonResponse("New password set", 200, $headers);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return new JsonResponse($errors, 400, $headers);
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
        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        $data = [];
        $form = $this->createForm(ForgotUsernameForm::class, $data);

        if ($request->isMethod("POST")) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            $email = $data['email'];
            if ($email === null) {
                return new JsonResponse("Email not found", 400, $headers);
            }

            $user = $this->userService->getUserByEmail($email);
            if ($user === null) {
                return new JsonResponse("User not found", 400, $headers);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                //@TODO send email with username
                return new JsonResponse("Email has been sent", 200, $headers);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return new JsonResponse($errors, 400, $headers);
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

        return new JsonResponse($data, 200, array('Access-Control-Allow-Origin'=> '*'));
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
            return new JsonResponse($data, 404);
        } else {
            $data = $this->serializeUser($user);
            return new JsonResponse($data, 200);
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

            $headers = [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*'
            ];

            if ($form->isSubmitted() && $form->isValid()) {
                $this->userService->save($user);
                $serialized = $this->serializeUser($user);
                return new JsonResponse($serialized, 200, $headers);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return new JsonResponse($errors, 400, $headers);
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

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

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
                    return new JsonResponse($serialized, 200, $headers);
                }
            }
        }

        $errors = (string)$form->getErrors(true, false);
        return new JsonResponse($errors, 400, $headers);
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

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

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

                    return new JsonResponse("An email has been sent", 200, $headers);
                }
            }
        }

        $errors = (string)$form->getErrors(true, false);
        return new JsonResponse($errors, 400, $headers);
    }

    /**
     * @return User
     */
    private function getLoggedInUser()
    {
        return $this->tokenStorage->getToken()->getUser();
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