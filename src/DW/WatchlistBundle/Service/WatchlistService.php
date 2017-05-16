<?php

namespace DW\WatchlistBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use DW\UserBundle\Entity\User;
use DW\WatchlistBundle\Criteria\WatchlistCriteria;
use DW\WatchlistBundle\Entity\Watchlist;
use DW\WatchlistBundle\Repository\WatchlistRepository;

class WatchlistService
{
    /**
     * @var WatchlistRepository
     */
    private $watchlistRepository;

    /**
     * @param WatchlistRepository $watchlistRepository
     */
    public function __construct(WatchlistRepository $watchlistRepository)
    {
        $this->watchlistRepository = $watchlistRepository;
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
}