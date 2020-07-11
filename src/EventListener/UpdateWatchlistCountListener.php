<?php

namespace App\EventListener;

use App\Event\WatchlistEvent;
use App\Event\WatchlistEvents;
use App\Service\DocumentaryService;
use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateWatchlistCountListener implements EventSubscriberInterface
{
    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param DocumentaryService $documentaryService
     * @param UserService $userService
     */
    public function __construct(
        DocumentaryService $documentaryService,
        UserService $userService
    )
    {
        $this->documentaryService = $documentaryService;
        $this->userService = $userService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            WatchlistEvents::WATCHLIST_CREATED => "onWatchlistCreated"
        );
    }

    /**
     * @param WatchlistEvent $watchlistEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onWatchlistCreated(WatchlistEvent $watchlistEvent)
    {
        $watchlist = $watchlistEvent->getWatchlist();

        $documentary = $watchlist->getDocumentary();
        $user = $watchlist->getUser();

        $this->documentaryService->updateWatchlistCountForDocumentary($documentary);
        $this->userService->updateWatchlistCountForUser($user);
    }
}