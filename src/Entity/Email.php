<?php

namespace App\Entity;

use App\Enum\OnMailingList;
use App\Enum\Subscribed;
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
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $subscribed;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $subscriptionKey;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $onMailingList;

    public function __construct()
    {
        $this->subscribed = Subscribed::YES;
        $this->onMailingList = OnMailingList::NO;
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
     * @return null|string
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
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
        return ($this->subscribed === Subscribed::YES);
    }

    /**
     * @return null|string
     */
    public function getSubscribed(): ?string
    {
        return $this->subscribed;
    }

    /**
     * @param string $subscribed
     */
    public function setSubscribed(string $subscribed): void
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

    /**
     * @return bool
     */
    public function isOnMailingList()
    {
        return ($this->onMailingList === OnMailingList::YES);
    }

    /**
     * @return null|string
     */
    public function getOnMailingList(): ?string
    {
        return $this->onMailingList;
    }

    /**
     * @param string $onMailingList
     */
    public function setOnMailingList(string $onMailingList)
    {
        $this->onMailingList = $onMailingList;
    }
}