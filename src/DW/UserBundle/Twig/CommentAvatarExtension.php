<?php

namespace DW\UserBundle\Twig;

use DW\CommentBundle\Entity\Comment;
use DW\UserBundle\Entity\User;
use DW\UserBundle\Service\UserService;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class CommentAvatarExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction("commentAvatar", [$this, "commentAvatar"])
        ];
    }

    /**
     * @param $comment
     * @param $filter
     * @return string
     */
    public function commentAvatar(Comment $comment, $filter) : string
    {
        $user = $comment->getUser();
        if ($user != null) {
            $avatarFile = $user->getAvatar();
            $avatar = 'uploads/avatar/' . $avatarFile;
            $avatar = $this->cacheManager->getBrowserPath($avatar, $filter);
        } else {
            $avatar = $this->userService->getGravatar($comment->getEmail());
        }

        return $avatar;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'comment_avatar_extension';
    }
}
