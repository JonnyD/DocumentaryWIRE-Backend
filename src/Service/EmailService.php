<?php

namespace App\Service;

use App\Criteria\EmailCriteria;
use App\Entity\Email;
use App\Enum\YesNo;
use App\Repository\EmailRepository;
use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\RequestStack;

class EmailService
{
    /**
     * @var EmailRepository
     */
    private $emailRepository;

    /**
     * @param EmailRepository $emailRepository
     */
    public function __construct(
        EmailRepository $emailRepository)
    {
        $this->emailRepository = $emailRepository;
    }

    /**
     * @param Email $email
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Email $email, $sync = true)
    {
        $this->emailRepository->save($email, $sync);
    }

    /**
     * @param EmailCriteria $criteria
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEmailsByCriteriaQueryBuilder(EmailCriteria $criteria)
    {
        return $this->emailRepository->findEmailsByCriteriaQueryBuilder($criteria);
    }

    /**
     * @param int $id
     * @return null|Email
     */
    public function getEmailById(int $id)
    {
        return $this->emailRepository->find($id);
    }

    /**
     * @param string $emailAddress
     * @return null|Email
     */
    public function getEmailByEmailAddress(string $emailAddress)
    {
        return $this->emailRepository->findOneBy([
            'email' => $emailAddress
        ]);
    }

    /**
     * @param string $emailAddress
     * @param string $subscriptionKey
     * @return null|object
     */
    public function getEmailByEmailAddressAndSubscriptionKey(string $emailAddress, string $subscriptionKey)
    {
        return $this->emailRepository->findOneBy([
            'email' => $emailAddress,
            'subscriptionKey' => $subscriptionKey
        ]);
    }

    /**
     * @return ArrayCollection|Email[]
     */
    public function getAllSubscribedEmails()
    {
        return $this->emailRepository->findBy([
            'subscribed' => true
        ]);
    }

    /**
     * @return ArrayCollection|Email[]
     */
    public function getAllEmails()
    {
        return $this->emailRepository->findAll();
    }

    /**
     * @param string $emailAddress
     * @throws \Doctrine\ORM\ORMException
     */
    public function subscribe(string $emailAddress)
    {
        $subscriptionKey = $resetKey = sha1(mt_rand(10000,99999).time().$emailAddress);;

        $existingEmail = $this->getEmailByEmailAddress($emailAddress);
        if ($existingEmail) {
            $existingEmail->setSubscribed(true);
            $existingEmail->setSubscriptionKey($subscriptionKey);
            $existingEmail->setUpdatedAt(new \DateTime());

            $this->save($existingEmail);
        } else {
            $email = new Email();
            $email->setCreatedAt(new \DateTime());
            $email->setEmail($emailAddress);
            $email->setSubscriptionKey($subscriptionKey);
            $email->setSubscribed(true);

            $this->save($email);
        }
    }

    /**
     * @param string $emailAddress
     * @throws \Doctrine\ORM\ORMException
     */
    public function unsubscribe(string $emailAddress)
    {
        $existingEmail = $this->getEmailByEmailAddress($emailAddress);
        $existingEmail->setSubscribed(YesNo::NO);
        $existingEmail->setUpdatedAt(new \DateTime());

        $this->save($existingEmail);

        return true;
    }
}