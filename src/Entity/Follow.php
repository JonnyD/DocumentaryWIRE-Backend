<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Traits\Timestampable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FollowRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Follow
{

    use Timestampable;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="subscribedFrom")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userFrom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="subscribedTo")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userTo;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUserFrom(): ?User
    {
        return $this->userFrom;
    }

    /**
     * @param User|null $userFrom
     * @return Follow
     */
    public function setUserFrom(?User $userFrom): self
    {
        $this->userFrom = $userFrom;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUserTo(): ?User
    {
        return $this->userTo;
    }

    /**
     * @param User|null $userTo
     * @return Follow
     */
    public function setUserTo(?User $userTo): self
    {
        $this->userTo = $userTo;

        return $this;
    }
}