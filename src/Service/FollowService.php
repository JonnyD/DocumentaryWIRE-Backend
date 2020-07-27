<?php

namespace App\Service;

use App\Criteria\FollowCriteria;
use App\Entity\Follow;
use App\Enum\Sync;
use App\Enum\UpdateTimestamps;
use App\Event\FollowEvent;
use App\Event\FollowEvents;
use App\Repository\FollowRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FollowService
{
    /**
     * @var FollowRepository
     */
    private $followRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param FollowRepository $followRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FollowRepository $followRepository,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->followRepository = $followRepository;
        $this->eventDispatcher = $eventDispatcher;
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
     * @param string $updateTimestamps
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Follow $follow, string $updateTimestamps = UpdateTimestamps::YES, string $sync = Sync::YES)
    {
        if ($updateTimestamps === UpdateTimestamps::YES) {
            $currentDateTime = new \DateTime();

            if ($follow->getCreatedAt() == null) {
                $follow->setCreatedAt($currentDateTime);
            } else {
                $follow->setUpdatedAt($currentDateTime);
            }
        }

        $this->followRepository->save($follow, $sync);

        $followEvent = new FollowEvent($follow);
        $this->eventDispatcher->dispatch($followEvent, FollowEvents::FOLLOW_SAVED);
    }

    /**
     * @param Follow $follow
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(Follow $follow)
    {
        $this->followRepository->remove($follow);

        $followEvent = new FollowEvent($follow);
        $this->eventDispatcher->dispatch($followEvent, FollowEvents::FOLLOW_SAVED); //@TODO change to follow_deleted
    }
}