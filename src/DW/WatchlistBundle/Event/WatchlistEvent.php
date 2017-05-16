<?php

namespace DW\WatchlistBundle\Event;

use DW\WatchlistBundle\Entity\Watchlist;
use Symfony\Component\EventDispatcher\Event;

class WatchlistEvent extends Event
{
    /**
     * @var Watchlist
     */
    private $watchlist;

    /**
     * @param Watchlist $watchlist
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
