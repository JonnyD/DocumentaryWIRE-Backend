<?php

namespace App\Hydrator;

use App\Entity\Follow;

class FollowHydrator implements HydratorInterface
{
    /**
     * @var Follow
     */
    private $follow;

    /**
     * @param Follow $follow
     */
    public function __construct(
        Follow $follow)
    {
        $this->follow = $follow;
    }

    public function toArray()
    {
        return [
            'id' => $this->follow->getId(),
            'userFrom' => [
                'id' => $this->follow->getUserFrom()->getId(),
                'username' => $this->follow->getUserFrom()->getUsername()
            ],
            'userTo' => [
                'id' => $this->follow->getUserTo()->getId(),
                'username' => $this->follow->getUserTo()->getUsername()
            ],
            'createdAt' => $this->follow->getCreatedAt(),
            'updatedAt' => $this->follow->getUpdatedAt()
        ];
    }
}