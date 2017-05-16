<?php

namespace DW\UserBundle\Controller\Publc;

use DW\UserBundle\Entity\User;
use DW\UserBundle\Enum\Role;
use DW\UserBundle\Enum\UserStatus;
use DW\UserBundle\Form\Registration;
use DW\UserBundle\Service\RoleService;
use DW\UserBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;

class SecurityController extends Controller
{
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(Registration::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if(!ctype_alnum(str_replace(array('-', '_'), '', $user->getUsername()))) {
                $form->addError(new FormError("Username can only contain letters, digits, - or _."));
            }

            $userService = $this->getUserService();
            $foundUser = $userService->getUserByUsername($user->getUsername());
            if ($foundUser != null) {
                $form->addError(new FormError("Username is already in use."));
            }

            $foundUser = $userService->getUserByEmail($user->getEmail());
            if ($foundUser != null) {
                $form->addError(new FormError("Email is already in use."));
            }

            if ($form->isValid()) {
                $passwordEncoder = $this->getPasswordEncoder();
                $password = $passwordEncoder->encodePassword($user, $user->getPassword());
                $user->setPassword($password);

                $roleService = $this->getRoleService();
                $role = $roleService->getRoleByName(Role::USER);
                $user->addRole($role);

                $activationKey = sha1(mt_rand(10000,99999).time().$user->getUsername());
                $user->setActivationKey($activationKey);

                $user->setStatus(UserStatus::ACTIVE);

                $userService->registerUser($user);

                $flashBag = $this->getFlashBag();
                $flashBag->get('registered');
                $flashBag->set('registered', "An email containing instructions to verify your email address has been sent. This email should be received within the next 10 minutes (usually instantly).");
            }
        }

        return $this->render('UserBundle:Publc/Security:register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('UserBundle:Publc/Security:login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function activateAction(Request $request)
    {
        $username = $request->query->get('username');
        $key = $request->query->get('key');

        $userService = $this->getUserService();
        $user = $userService->getUserByUsername($username);

        if ($user == null || $user->getActivationKey() != $key) {
            return $this->render('UserBundle:Publc/Security:invalidActivationKey.html.twig');
        }

        $user->setActivatedAt(new \DateTime());
        $userService->confirmUser($user);

        return $this->render('UserBundle:Publc/Security:activationSuccessful.html.twig');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->get('dw.user_service');
    }

    /**
     * @return RoleService
     */
    private function getRoleService()
    {
        return $this->get('dw.role_service');
    }

    /**
     * @return PasswordEncoder
     */
    private function getPasswordEncoder()
    {
        return $this->get('security.password_encoder');
    }

    /**
     * @return FlashBag
     */
    private function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }
}