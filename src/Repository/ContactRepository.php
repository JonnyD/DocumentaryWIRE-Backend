<?php

namespace App\Repository;

use App\Criteria\CategoryCriteria;
use App\Criteria\ContactCriteria;
use App\Entity\Category;
use App\Entity\Contact;
use App\Enum\Sync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param ContactCriteria $criteria
     * @return Contact
     */
    public function findContactByCriteria(ContactCriteria $criteria)
    {
        $criteria->setLimit(1);
        $qb = $this->findContactsByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param ContactCriteria $criteria
     * @return ArrayCollection|Contact[]
     */
    public function findContactsByCriteria(ContactCriteria $criteria)
    {
        $qb = $this->findContactsByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param ContactCriteria $criteria
     * @return QueryBuilder
     */
    public function findContactsByCriteriaQueryBuilder(ContactCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('contact')
            ->from('App\Entity\Contact', 'contact');

        if ($criteria->getMessage()) {
            $qb->andWhere('contact.message = :message')
                ->setParameter('message', $criteria->getMessage());
        }

        if ($criteria->getSubject()) {
            $qb->andWhere('contact.subject = :subject')
                ->setParameter('subject', $criteria->getSubject());
        }

        if ($criteria->getEmailAddress()) {
            $qb->andWhere('contact.emailAddress = :emailAddress')
                ->setParameter('emailAddress', $criteria->getEmailAddress());
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
     * @param Contact $contact
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Contact $contact, string $sync = Sync::YES)
    {
        $this->getEntityManager()->persist($contact);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
