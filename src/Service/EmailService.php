<?php

namespace App\Service;

use App\Criteria\EmailCriteria;
use App\Entity\Email;
use App\Enum\Subscribed;
use App\Enum\Sync;
use App\Repository\EmailRepository;
use App\Repository\ActivityRepository;
use Carbon\Carbon;
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
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Email $email, string $sync = Sync::YES)
    {
        if ($email->getCreatedAt() == null) {
            $email->setCreatedAt(new \DateTime());
        } else {
            $email->setUpdatedAt(new \DateTime());
        }

        $this->emailRepository->save($email, $sync);
    }

    /**
     * @param Email[] $emails
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveAll($emails)
    {
        foreach ($emails as $email) {
            $this->save($email, Sync::NO);
        }

        $this->emailRepository->flush();
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
     * @param Email[] $emails
     * @param int $chunkSize
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateSubscriptionKeysForEmailsUsingModulos(array $emails, int $chunkSize)
    {
        $emailsCount = count($emails);

        $editedEmails = [];
        for ($i = 0; $i < $emailsCount; $i++) {
            $email = $emails[$i];

            $subscriptionKey = sha1(mt_rand(10000,99999).time().$email->getEmail());
            $email->setSubscriptionKey($subscriptionKey);

            $editedEmails[] = $email;

            $isIndexEqualToChunkSize = ($i % $chunkSize) === 0;
            $isIndexEqualToEmailsCount = $i === ($emailsCount - 1);

            if ($isIndexEqualToChunkSize || $isIndexEqualToEmailsCount) {
                $this->saveAll($editedEmails);
                $editedEmails = [];
            }
        }
    }

    /**
     * @param Email[] $emails
     * @param int $chunkSize
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateSubscriptionKeysForEmailsUsingArrayChunk(array $emails, int $chunkSize)
    {
        $chunks = array_chunk($emails, $chunkSize, true);

        /** @var Email[] $chunkEmails */
        foreach ($chunks as $chunkEmails) {
            $editedEmails = [];

            foreach ($chunkEmails as $email) {
                $subscriptionKey = sha1(mt_rand(10000,99999).time().$email->getEmail());
                $email->setSubscriptionKey($subscriptionKey);

                $editedEmails[] = $email;
            }

            if (count($editedEmails) > 0) {
                $this->saveAll($editedEmails);
            }
        }
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
            $existingEmail->setSubscribed(Subscribed::YES);
            $existingEmail->setSubscriptionKey($subscriptionKey);
            $existingEmail->setUpdatedAt(new \DateTime());

            $this->save($existingEmail);
        } else {
            $email = new Email();
            $email->setCreatedAt(new \DateTime());
            $email->setEmail($emailAddress);
            $email->setSubscriptionKey($subscriptionKey);
            $email->setSubscribed(Subscribed::YES);

            $this->save($email);
        }
        
        //@TODO add to mailing list
    }

    /**
     * @param string $emailAddress
     * @throws \Doctrine\ORM\ORMException
     */
    public function unsubscribe(string $emailAddress)
    {
        $existingEmail = $this->getEmailByEmailAddress($emailAddress);
        $existingEmail->setSubscribed(Subscribed::NO);
        $existingEmail->setUpdatedAt(new \DateTime());

        $this->save($existingEmail);

        return true;
    }
}