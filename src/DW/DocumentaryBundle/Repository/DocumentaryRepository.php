<?php

namespace DW\DocumentaryBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use DW\DocumentaryBundle\Criteria\DocumentaryCriteria;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\DocumentaryBundle\Enum\OrderBy;

class DocumentaryRepository extends EntityRepository
{
    /**
     * @param DocumentaryCriteria $criteria
     * @return Documentary
     */
    public function findDocumentaryByCriteria(DocumentaryCriteria $criteria)
    {
        $criteria->setLimit(1);
        $qb = $this->findDocumentariesByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param DocumentaryCriteria $criteria
     * @return ArrayCollection|Documentary[]
     */
    public function findDocumentariesByCriteria(DocumentaryCriteria $criteria)
    {
        $qb = $this->findDocumentariesByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $query->useResultCache(true, 3600, 'my_region')
            ->useQueryCache(true);
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param DocumentaryCriteria $criteria
     * @return QueryBuilder
     */
    public function findDocumentariesByCriteriaQueryBuilder(DocumentaryCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('documentary')
            ->from('DW\DocumentaryBundle\Entity\Documentary', 'documentary');

        if (!empty($criteria->isFeatured())) {
            $qb->andWhere('documentary.featured = :featured')
                ->setParameter('featured', $criteria->isFeatured());
        }

        if ($criteria->getStatus()) {
            $qb->andWhere('documentary.status = :status')
                ->setParameter('status', $criteria->getStatus());
        }

        if ($criteria->getCategory()) {
            $qb->andWhere('documentary.category = :category')
                ->setParameter('category', $criteria->getCategory());
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
     * @param Documentary $documentary
     * @param bool $sync
     */
    public function save(Documentary $documentary, bool $sync = true)
    {
        $this->getEntityManager()->persist($documentary);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}