<?php

namespace App\Controller;

use App\Service\WatchlistService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;

class WatchlistController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var WatchlistService
     */
    private $watchlistService;

    /**
     * @param WatchlistService $watchlistService
     */
    public function __construct(WatchlistService $watchlistService)
    {
        $this->watchlistService = $watchlistService;
    }
}