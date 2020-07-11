<?php

namespace App\Event;

use App\Entity\Watchlist;

class WatchlistEvent
{
    /**
     * @var Watchlist
     */
    protected $watchlist;

    /**
     * @param Watchlist
     */
    public function __construct(Watchlist $watchlist)
    {
        $this->watchlist = $watchlist;
    }

    /**
     * @return Watchlist
     */
    public function getWatchlist()
    {
        return $this->watchlist;
    }
}