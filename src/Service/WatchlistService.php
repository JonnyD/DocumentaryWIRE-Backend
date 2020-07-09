<?php

namespace App\Service;

use App\Criteria\WatchlistCriteria;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class WatchlistService
{
    /**
     * @var WatchlistRepository
     */
    private $watchlistRepository;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param WatchlistRepository $watchlistRepository
     * @param RequestStack $requestStack
     */
    public function __construct(
        WatchlistRepository $watchlistRepository,
        RequestStack $requestStack)
    {
        $this->watchlistRepository = $watchlistRepository;
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
    public function getWatchlistByCriteriaQueryBuilder(WatchlistCriteria $criteria)
    {
        return $this->watchlistRepository->findWatchlistByCriteriaQueryBuilder($criteria);
    }
}