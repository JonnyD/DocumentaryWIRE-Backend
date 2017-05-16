<?php

namespace DW\UserBundle\Service;

use DW\UserBundle\Entity\Facebook;
use DW\UserBundle\Entity\User;
use DW\UserBundle\Repository\FacebookRepository;

class FacebookService
{
    /**
     * @var FacebookRepository
     */
    private $facebookRepository;

    /**
     * @param FacebookRepository $facebookRepository
     */
    public function __construct(FacebookRepository $facebookRepository)
    {
        $this->facebookRepository = $facebookRepository;
    }

    /**
     * @param string $facebookId
     * @return null|Facebook
     */
    public function getFacebookByFacebookId(string $facebookId)
    {
        return $this->facebookRepository->findOneBy([
            'facebookId' => $facebookId
        ]);
    }

    /**
     * @param User $user
     * @return null|Facebook
     */
    public function getFacebookByUser(User $user)
    {
        return $this->facebookRepository->findOneBy([
            'user' => $user
        ]);
    }

    /**
     * @param Facebook $facebook
     * @param bool $sync
     */
    public function save(Facebook $facebook, bool $sync = true)
    {
        $this->facebookRepository->save($facebook, $sync);
    }
}