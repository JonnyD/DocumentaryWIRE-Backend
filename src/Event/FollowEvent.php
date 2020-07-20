<?php

namespace App\Event;

use App\Entity\Follow;
use App\Entity\User;

class FollowEvent
{
    /**
     * @var Follow
     */
    private $follow;

    /**
     * @param Follow $follow
     */
    public function __construct(Follow $follow)
    {
        $this->follow = $follow;
    }

    /**
     * @return Follow
     */
    public function getFollow(): Follow
    {
        return $this->follow;
    }

    /**
     * @param Follow $follow
     */
    public function setFollow(Follow $follow): void
    {
        $this->follow = $follow;
    }
}