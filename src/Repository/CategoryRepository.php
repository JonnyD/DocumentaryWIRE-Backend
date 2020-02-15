<?php

namespace App\Repository;

use App\Criteria\CategoryCriteria;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Category::class);
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
     * @return Category|null
     */
    public function findAllCategoriesOrderedByName()
    {
        return $this->createQueryBuilder('c')
            ->where('c.documentaryCount > 0')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /**
     * @param CategoryCriteria $criteria
     * @return Category
     */
    public function findCommentByCriteria(CategoryCriteria $criteria)
    {
        $criteria->setLimit(1);
        $qb = $this->findCategoriesByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param CategoryCriteria $criteria
     * @return ArrayCollection|Category[]
     */
    public function findCategoriesByCriteria(CategoryCriteria $criteria)
    {
        $qb = $this->findCategoriesByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $query->useResultCache(true, 3600, 'my_region')
            ->useQueryCache(true);
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param CategoryCriteria $criteria
     * @return QueryBuilder
     */
    public function findCategoriesByCriteriaQueryBuilder(CategoryCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('category')
            ->from('App\Entity\Category', 'category');

        if ($criteria->getStatus()) {
            $qb->andWhere('category.status = :status')
                ->setParameter('status', $criteria->getStatus());
        }

        if ($criteria->getGreaterThanEqual() != null) {
            $qb->andWhere('category.documentaryCount >= :greaterThanEqual')
                ->setParameter('greaterThanEqual', $criteria->getGreaterThanEqual());
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
     * @param Category $category
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Category $category, bool $sync = true)
    {
        $this->getEntityManager()->persist($category);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
