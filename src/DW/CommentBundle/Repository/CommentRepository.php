<?php

namespace DW\CommentBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use DW\CommentBundle\Criteria\CommentCriteria;
use DW\CommentBundle\Entity\Comment;

class CommentRepository extends EntityRepository
{
    /**
     * @param CommentCriteria $criteria
     * @return QueryBuilder
     */
    public function findAllByCriteriaQueryBuilder(CommentCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('comment')
            ->from('DW\CommentBundle\Entity\Comment', 'comment');

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

        if ($criteria->getSort()) {
            foreach ($criteria->getSort() as $column => $direction) {
                if (strpos($column, '.') === false) {
                    $qb->addOrderBy($qb->getRootAliases()[0] . '.' . $column, $direction);
                } else {
                    $qb->addOrderBy($column, $direction);
                }
            }
        }

        if ($criteria->getLimit()) {
            $qb->setMaxResults($criteria->getLimit());
        }

        return $qb;
    }

    /**
     * @param CommentCriteria $criteria
     * @return ArrayCollection|Comment[]
     */
    public function findAllByCriteria(CommentCriteria $criteria)
    {
        $qb = $this->findAllByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
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

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}