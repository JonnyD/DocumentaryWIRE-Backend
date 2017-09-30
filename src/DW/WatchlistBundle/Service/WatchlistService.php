<?php

namespace DW\WatchlistBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\UserBundle\Entity\User;
use DW\WatchlistBundle\Criteria\WatchlistCriteria;
use DW\WatchlistBundle\Entity\Watchlist;
use DW\WatchlistBundle\Event\WatchlistEvent;
use DW\WatchlistBundle\Event\WatchlistEvents;
use DW\WatchlistBundle\Repository\WatchlistRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @param WatchlistRepository $watchlistRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        WatchlistRepository $watchlistRepository,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->watchlistRepository = $watchlistRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param User $user
     * @return ArrayCollection|Watchlist[]
     */
    public function getWatchlistedByUser(User $user)
    {
        $criteria = new WatchlistCriteria();
        $criteria->setUser($user);

        return $this->watchlistRepository->findAllByCriteria($criteria);
    }

    /**
     * @param User $user
     * @param Documentary $documentary
     * @return Watchlist
     */
    public function getWatchlistByUserAndDocumentary(User $user, Documentary $documentary)
    {
        $criteria = new WatchlistCriteria();
        $criteria->setUser($user);
        $criteria->setDocumentary($documentary);

        return $this->watchlistRepository->findByCriteria($criteria);
    }

    /**
     * @param User $user
     * @param Documentary $documentary
     * @return bool
     */
    public function hasWatchlisted(User $user, Documentary $documentary)
    {
        $criteria = new WatchlistCriteria();
        $criteria->setUser($user);
        $criteria->setDocumentary($documentary);

        $watchlist = $this->watchlistRepository->findByCriteria($criteria);
        if (!$watchlist) {
            return true;
        }

        return false;
    }

    /**
     * @param WatchlistCriteria $criteria
     * @return ArrayCollection|Watchlist[]
     */
    public function getWatchlistedByCriteria(WatchlistCriteria $criteria)
    {
        return $this->watchlistRepository->findAllByCriteria($criteria);
    }

    /**
     * @param User $user
     * @param Documentary $documentary
     */
    public function watchlistDocumentary(User $user, Documentary $documentary)
    {
        $watchlist = new Watchlist();
        $watchlist->setUser($user);
        $watchlist->setDocumentary($documentary);

        $this->watchlistRepository->save($watchlist);

        $watchlistEvent = new WatchlistEvent($watchlist);
        $this->eventDispatcher->dispatch(WatchlistEvents::WATCHLISTED, $watchlistEvent);
    }

    /**
     * @param Watchlist $watchlist
     */
    public function unwatchlistDocumentary(Watchlist $watchlist)
    {
        $this->watchlistRepository->remove($watchlist);

        $watchlistEvent = new WatchlistEvent($watchlist);
        $this->eventDispatcher->dispatch(WatchlistEvents::UNWATCHLISTED, $watchlistEvent);
    }
}