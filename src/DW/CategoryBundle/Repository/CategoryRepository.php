<?php

namespace DW\CategoryBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use DW\CategoryBundle\Entity\Category;

class CategoryRepository extends EntityRepository
{
    /**
     * @return ArrayCollection|Category[]
     */
    public function findAllWithDocumentaries()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('category')
            ->from('DW\CategoryBundle\Entity\Category', 'category')
            ->where('category.count > 0')
            ->orderBy('category.name', 'ASC');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }
}