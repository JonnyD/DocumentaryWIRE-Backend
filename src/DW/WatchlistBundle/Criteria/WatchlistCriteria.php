<?php

namespace DW\WatchlistBundle\Criteria;

use DW\DocumentaryBundle\Entity\Documentary;
use DW\UserBundle\Entity\User;

class WatchlistCriteria
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Documentary
     */
    private $documentary;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Documentary
     */
    public function getDocumentary()
    {
        return $this->documentary;
    }

    /**
     * @param Documentary $documentary
     */
    public function setDocumentary(Documentary $documentary)
    {
        $this->documentary = $documentary;
    }
}