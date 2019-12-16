<?php

namespace App\Entity;

use App\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmailRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Email
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
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $subscribed;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $subscriptionKey;

    public function __construct()
    {
        $this->subscribed = true;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isSubscribed(): ?bool
    {
        return $this->subscribed;
    }

    /**
     * @param bool $subscribed
     */
    public function setSubscribed(bool $subscribed): void
    {
        $this->subscribed = $subscribed;
    }

    /**
     * @return string
     */
    public function getSubscriptionKey(): ?string
    {
        return $this->subscriptionKey;
    }

    /**
     * @param string $subscriptionKey
     */
    public function setSubscriptionKey(string $subscriptionKey): void
    {
        $this->subscriptionKey = $subscriptionKey;
    }
}