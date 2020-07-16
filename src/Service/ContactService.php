<?php

namespace App\Service;

use App\Criteria\ContactCriteria;
use App\Entity\Category;
use App\Entity\Contact;
use App\Enum\Sync;
use App\Repository\ContactRepository;

class ContactService
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @param ContactRepository $contactRepository
     */
    public function __construct(
        ContactRepository $contactRepository
    )
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * @return Category[]
     */
    public function getAllContacts()
    {
        return $this->contactRepository->findAll();
    }

    /**
     * @param ContactCriteria $criteria
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getContactsByQueryBuilder(ContactCriteria $criteria)
    {
        return $this->contactRepository->findContactsByCriteriaQueryBuilder($criteria);
    }

    /**
     * @param int $id
     * @return Category|null
     */
    public function getContactById(int $id)
    {
        return $this->contactRepository->find($id);
    }

    /**
     * @param Contact $contact
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Contact $contact, string $sync = Sync::YES)
    {
        if ($contact->getCreatedAt() == null) {
            $contact->setCreatedAt(new \DateTime());
        } else {
            $contact->setUpdatedAt(new \DateTime());
        }

        $this->contactRepository->save($contact, $sync);
    }
}