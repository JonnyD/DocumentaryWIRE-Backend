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

    /**
     * @return array
     */
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

    /**
     * @param array $data
     * @return Contact
     */
    public function toObject(array $data)
    {
        $subject = $data['subject'];
        if (isset($subject)) {
            $this->contact->setSubject($subject);
        }

        $email = $data['emailAddress'];
        if (isset($email)) {
            $this->contact->setEmailAddress($email);
        }

        $message = $data['message'];
        if (isset($message)) {
            $this->contact->setMessage($message);
        }

        return $this->contact;
    }
}