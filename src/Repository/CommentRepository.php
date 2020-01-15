<?php

namespace App\Repository;

use App\Criteria\CommentCriteria;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
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
    public function findOneBySomeField($value): ?Comment
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
     * @param CommentCriteria $criteria
     * @return Comment
     */
    public function findCommentByCriteria(CommentCriteria $criteria)
    {
        $criteria->setLimit(1);
        $qb = $this->findCommentsByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param CommentCriteria $criteria
     * @return ArrayCollection|Comment[]
     */
    public function findDCommentsByCriteria(CommentCriteria $criteria)
    {
        $qb = $this->findCommentsByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $query->useResultCache(true, 3600, 'my_region')
            ->useQueryCache(true);
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param CommentCriteria $criteria
     * @return QueryBuilder
     */
    public function findCommentsByCriteriaQueryBuilder(CommentCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('comment')
            ->from('App\Entity\Comment', 'comment');

        if ($criteria->getStatus()) {
            $qb->andWhere('comment.status = :status')
                ->setParameter('status', $criteria->getStatus());
        }

        if ($criteria->getDocumentary()) {
            $qb->andWhere('comment.documentary = :documentary')
                ->setParameter('documentary', $criteria->getDocumentary());
        }

        if ($criteria->getUser()) {
            $qb->andWhere('comment.user = :user')
                ->setParameter('user', $criteria->getUser());
        }

        if ($criteria->getEmail()) {
            $qb->andWhere('comment.email = :email')
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
     * @param string $email
     * @return Comment[]
     */
    public function findCommentsByEmail(string $email)
    {
        return $this->findBy([
            'email' => $email
        ]);
    }

    /**
     * @param Comment $comment
     * @param bool $sync
     */
    public function save(Comment $comment, bool $sync = true)
    {
        $this->getEntityManager()->persist($comment);
        if ($sync) {
            $this->flush();
        }
    }

    /**
     * @param Comment $comment
     * @param bool $sync
     */
    public function remove(Comment $comment, bool $sync = true)
    {
        $this->getEntityManager()->remove($comment);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
