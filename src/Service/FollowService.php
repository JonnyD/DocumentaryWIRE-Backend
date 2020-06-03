<?php

namespace App\Service;

use App\Criteria\FollowCriteria;
use App\Entity\Follow;
use App\Repository\FollowRepository;

class FollowService
{
    /**
     * @var FollowRepository
     */
    private $followRepository;

    /**
     * @param FollowRepository $followRepository
     */
    public function __construct(
        FollowRepository $followRepository
    )
    {
        $this->followRepository = $followRepository;
    }

    /**
     * @return Follow[]
     */
    public function getAllFollows()
    {
        return $this->followRepository->findAll();
    }

    /**
     * @param int $id
     * @return null|Follow
     */
    public function getFollowById(int $id)
    {
        return $this->followRepository->find($id);
    }

    /**
     * @param FollowCriteria $criteria
     * @return Follow
     */
    public function getFollowByCriteria(FollowCriteria $criteria)
    {
        return $this->followRepository->findFollowByCriteria($criteria);
    }

    /**
     * @param FollowCriteria $criteria
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFollowsByCriteriaQueryBuilder(FollowCriteria $criteria)
    {
        return $this->followRepository->findFollowByCriteriaQueryBuilder($criteria);
    }

    /**
     * @param Follow $follow
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Follow $follow, bool $sync = true)
    {
        if ($follow->getCreatedAt() == null) {
            $follow->setCreatedAt(new \DateTime());
        } else {
            $follow->setUpdatedAt(new \DateTime());
        }

        $this->followRepository->save($follow, $sync);
    }

    /**
     * @param Follow $follow
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(Follow $follow)
    {
        $this->followRepository->remove($follow);
    }
}