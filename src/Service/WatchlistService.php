<?php

namespace App\Service;

use App\Criteria\WatchlistCriteria;
use App\Entity\Documentary;
use App\Enum\Sync;
use App\Enum\UpdateTimestamps;
use App\Enum\YesNo;
use App\Event\WatchlistEvent;
use App\Event\WatchlistEvents;
use App\Repository\WatchlistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use App\Criteria\ActivityCriteria;
use App\Entity\Activity;
use App\Enum\ActivityType;
use App\Enum\ComponentType;
use App\Enum\ActivityOrderBy;
use App\Repository\ActivityRepository;
use App\Enum\Order;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Watchlist;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class WatchlistService
{
    /**
     * @var WatchlistRepository
     */
    private $watchlistRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param WatchlistRepository $watchlistRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     */
    public function __construct(
        WatchlistRepository $watchlistRepository,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack)
    {
        $this->watchlistRepository = $watchlistRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param int $id
     * @return Watchlist|null
     */
    public function getWatchlistById(int $id)
    {
        return $this->watchlistRepository->find($id);
    }

    /**
     * @param WatchlistCriteria $criteria
     * @return Watchlist[]|ArrayCollection
     */
    public function getWatchlistsByCriteria(WatchlistCriteria $criteria)
    {
        return $this->watchlistRepository->findWatchlistsByCriteria($criteria);
    }

    /**
     * @param WatchlistCriteria $criteria
     * @return QueryBuilder
     */
    public function getWatchlistsByCriteriaQueryBuilder(WatchlistCriteria $criteria)
    {
        return $this->watchlistRepository->findWatchlistsByCriteriaQueryBuilder($criteria);
    }

    /**
     * @param User $user
     * @param Documentary $documentary
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getWatchlistByUserAndDocumentary(User $user, Documentary $documentary)
    {
        $criteria = new WatchlistCriteria();
        $criteria->setUser($user);
        $criteria->setDocumentary($documentary);
        $criteria->setLimit(1);

        $watchlist = $this->watchlistRepository->findWatchlistByCriteria($criteria);
        return $watchlist;
    }

    /**
     * @param Watchlist $watchlist
     * @throws \Doctrine\ORM\ORMException
     */
    public function createWatchlist(Watchlist $watchlist)
    {
        $this->save($watchlist, UpdateTimestamps::YES, Sync::YES);

        $watchlistEvent = new WatchlistEvent($watchlist);
        $this->eventDispatcher->dispatch($watchlistEvent, WatchlistEvents::WATCHLIST_CREATED);
    }

    /**
     * @param Watchlist $watchlist
     * @param string $updateTimestamps
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Watchlist $watchlist, string $updateTimestamps = UpdateTimestamps::YES, string $sync = YesNo::YES)
    {
        if ($updateTimestamps === UpdateTimestamps::YES) {
            $currentDateTime = new \DateTime();

            if ($watchlist->getCreatedAt() == null) {
                $watchlist->setCreatedAt($currentDateTime);
            } else {
                $watchlist->setUpdatedAt($currentDateTime);
            }
        }

        $this->watchlistRepository->save($watchlist, $sync);
    }
}