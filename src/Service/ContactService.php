<?php

namespace App\Service;

use App\Criteria\ContactCriteria;
use App\Entity\Category;
use App\Entity\Contact;
use App\Enum\Sync;
use App\Enum\UpdateTimestamps;
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
     * @param string $updateTimestamps
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Contact $contact, string $updateTimestamps = UpdateTimestamps::YES, string $sync = Sync::YES)
    {
        if ($updateTimestamps === UpdateTimestamps::YES) {
            $currentDateTime = new \DateTime();

            if ($contact->getCreatedAt() == null) {
                $contact->setCreatedAt($currentDateTime);
            } else {
                $contact->setUpdatedAt($currentDateTime);
            }
        }

        $this->contactRepository->save($contact, $sync);
    }
}