<?php

namespace App\Hydrator;


use App\Entity\Email;

class EmailHydrator implements HydratorInterface
{
    /**
     * @var Email
     */
    private $email;

    /**
     * @param Email $email
     */
    public function __construct(
        Email $email)
    {
        $this->email = $email;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->email->getId(),
            'email' => $this->email->getEmail(),
            'source' => $this->email->getSource(),
            'subscribed' => $this->email->getSubscribed(),
            'subscriptionKey' => $this->email->getSubscriptionKey(),
            'createdAt' => $this->email->getCreatedAt(),
            'updatedAt' => $this->email->getUpdatedAt()
        ];

        return $array;
    }

    public function toObject(array $data)
    {
        // TODO: Implement toObject() method.
    }
}