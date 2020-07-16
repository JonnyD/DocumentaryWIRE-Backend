<?php

namespace App\Hydrator;

use App\Entity\Contact;

class ContactHydrator implements HydratorInterface
{
    /**
     * @var Contact
     */
    private $contact;

    /**
     * @param Contact $contact
     */
    public function __construct(
        Contact $contact)
    {
        $this->contact = $contact;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->contact->getId(),
            'subject' => $this->contact->getSubject(),
            'message' => $this->contact->getMessage(),
            'emailAddress' => $this->contact->getEmailAddress(),
            'createdAt' => $this->contact->getCreatedAt(),
            'updatedAt' => $this->contact->getUpdatedAt()
        ];

        return $array;
    }
}