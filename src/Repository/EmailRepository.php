<?php

namespace App\Repository;

use App\Criteria\EmailCriteria;
use App\Entity\Email;
use App\Enum\Sync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Email::class);
    }

    /**
     * @param EmailCriteria $criteria
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findEmailsByCriteriaQueryBuilder(EmailCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('email')
            ->from('App\Entity\Email', 'email');

        $subscribed = $criteria->getSubscribed();
        if (isset($subscribed)) {
            $qb->andWhere('email.subscribed = :subscribed')
                ->setParameter('subscribed', $subscribed);
        }

        if ($criteria->getEmail() != null) {
            $qb->andWhere('email.email = :email')
                ->setParameter('email', $criteria->getEmail());
        }

        if ($criteria->getSort()) {
            foreach ($criteria->getSort() as $column => $direction) {
                $qb->addOrderBy($qb->getRootAliases()[0] . '.' . $column, $direction);
            }
        }

        if ($criteria->getLimit()) {
            $qb->setMaxResults($criteria->getLimit());
        }

        return $qb;
    }

    /**
     * @param Email $email
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Email $email, string $sync = Sync::YES)
    {
        $this->getEntityManager()->persist($email);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
