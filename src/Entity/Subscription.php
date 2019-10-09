<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Traits\Timestampable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionRepository")
 *
 * @ORM\HasLifecycleCallbacks
 */
class Subscription
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

    public function getUserFrom(): ?User
    {
        return $this->userFrom;
    }

    public function setUserFrom(?User $userFrom): self
    {
        $this->userFrom = $userFrom;

        return $this;
    }

    public function getUserTo(): ?User
    {
        return $this->userTo;
    }

    public function setUserTo(?User $userTo): self
    {
        $this->userTo = $userTo;

        return $this;
    }
}