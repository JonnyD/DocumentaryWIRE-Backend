<?php

namespace DW\UserBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\UserBundle\Service\UserService;

class LatestMembersExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction("latestMembers", [$this, "latestMembers"])
        ];
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function latestMembers(int $limit)
    {
        return $this->userService->getLatestMembers($limit);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'latest_members_extension';
    }
}
