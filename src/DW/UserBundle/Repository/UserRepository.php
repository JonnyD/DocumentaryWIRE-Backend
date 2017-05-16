<?php

namespace DW\UserBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use DW\UserBundle\Criteria\UserCriteria;
use DW\UserBundle\Entity\User;

class UserRepository extends EntityRepository
{
    /**
     * @param UserCriteria $criteria
     * @return ArrayCollection|User[]
     */
    public function findAllByCriteria(UserCriteria $criteria) : array
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('user')
            ->from('DW\UserBundle\Entity\User', 'user');

        if (!empty($criteria->isActivated())) {
            if ($criteria->isActivated()) {
                $qb->andWhere('user.activatedAt IS NOT NULL');
            } else {
                $qb->andWhere('user.activatedAt IS NULL');
            }
        }

        if ($criteria->getSort()) {
            foreach ($criteria->getSort() as $column => $direction) {
                $qb->addOrderBy($qb->getRootAliases()[0] . '.' . $column, $direction);
            }
        }

        if ($criteria->getLimit()) {
            $qb->setMaxResults($criteria->getLimit());
        }

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param User $user
     * @param bool $sync
     */
    public function save(User $user, bool $sync = true)
    {
        $this->getEntityManager()->persist($user);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}