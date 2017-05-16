<?php

namespace DW\UserBundle\Twig;

use DW\UserBundle\Entity\User;
use DW\UserBundle\Service\UserService;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class AvatarExtension extends \Twig_Extension
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**s
     * @param UserService $userService
     * @param CacheManager $cacheManager
     */
    public function __construct(UserService $userService, CacheManager $cacheManager)
    {
        $this->userService = $userService;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("avatar", [$this, "avatar"])
        ];
    }

    /**
     * @param $user
     * @param $filter
     * @return string
     */
    public function avatar($user, $filter) : string
    {
        if ($user instanceof User) {
            $avatarFile = $user->getAvatar();
            $email = $user->getEmail();
        } else {
            $avatarFile = $user["avatar"];
            $email = $user["email"];
        }

        if ($avatarFile == null) {
            $avatar = $this->userService->getGravatar($email);
        } else {
            $avatar = 'uploads/avatar/' . $avatarFile;
            $avatar = $this->cacheManager->getBrowserPath($avatar, $filter);
        }

        return $avatar;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'avatar_extension';
    }
}
