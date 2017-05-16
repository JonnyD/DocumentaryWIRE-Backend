<?php

namespace DW\WatchlistBundle\Entity;

use DW\DocumentaryBundle\Entity\Documentary;
use DW\UserBundle\Entity\User;
use Gedmo\Timestampable\Traits\Timestampable;

class Watchlist
{
    use Timestampable;

    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Documentary
     */
    private $documentary;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

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