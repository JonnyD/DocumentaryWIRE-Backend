<?php

namespace DW\UserBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\UserBundle\Service\UserService;

class RecentlyActiveMembersExtension extends \Twig_Extension
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("recentlyActiveMembers", [$this, "recentlyActiveMembers"])
        ];
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function recentlyActiveMembers(int $limit)
    {
        return $this->userService->getRecentlyActiveMembers($limit);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recently_active_members_extension';
    }
}
