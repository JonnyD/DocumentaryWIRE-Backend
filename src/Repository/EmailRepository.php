<?php

namespace App\Repository;

use App\Criteria\EmailCriteria;
use App\Entity\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class EmailRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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

        if ($criteria->isSubscribed() != null) {
            $qb->andWhere('email.subscribed = :subscribed')
                ->setParameter('subscribed', $criteria->isSubscribed());
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
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Email $email, bool $sync = true)
    {
        $this->getEntityManager()->persist($email);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
