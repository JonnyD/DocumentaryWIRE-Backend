<?php

namespace App\Hydrator;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class UserHydrator implements HydratorInterface
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $loggedInUser;

    /**
     * @var string
     */
    private $isRoleAdmin;

    /**
     * @param User $user
     * @param Request $request
     * @param User $loggedInUser
     * @param string $isRoleAdmin
     */
    public function __construct(
        User $user,
        Request $request,
        User $loggedInUser,
        string $isRoleAdmin)
    {
        $this->user = $user;
        $this->request = $request;
        $this->loggedInUser = $loggedInUser;
        $this->isRoleAdmin = $isRoleAdmin;
    }

    public function toArray()
    {
        $array = [
            'name' => $this->user->getName(),
            'username' => $this->user->getUsername(),
            'avatar' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $this->user->getAvatar(),
            'roles' => $this->user->getRoles(),
            'createdAt' => $this->user->getCreatedAt(),
            'lastLogin' => $this->user->getLastLogin()
        ];

        $isUser = false;
        if ($this->loggedInUser === $this->user) {
            $isUser = true;
        }

        if ($isUser || $this->isRoleAdmin) {
            $array['id'] = $this->user->getId();
            $array['usernameCanonical'] = $this->user->getUsernameCanonical();
            $array['email'] = $this->user->getEmail();
            $array['emailCanonical'] = $this->user->getEmailCanonical();
            $array['resetKey'] = $this->user->getResetKey();
            $array['activatedAt'] = $this->user->getActivatedAt();
            $array['enabled'] = $this->user->isEnabled();
            $array['password'] = $this->user->getPassword();
            $array['lastLogin'] = $this->user->getLastLogin();
            $array['confirmationToken'] = $this->user->getConfirmationToken();
            $array['passwordRequestedAt'] = $this->user->getPasswordRequestedAt();
            $array['updatedAt'] = $this->user->getUpdatedAt();
        }

        return $array;
    }
}