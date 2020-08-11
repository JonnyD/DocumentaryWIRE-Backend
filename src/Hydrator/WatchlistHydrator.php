<?php

namespace App\Hydrator;

use App\Entity\Watchlist;
use Symfony\Component\HttpFoundation\Request;

class WatchlistHydrator implements HydratorInterface
{
    /**
     * @var Watchlist
     */
    private $watchlist;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Watchlist $watchlist
     * @param Request $request
     */
    public function __construct(
        Watchlist $watchlist,
        Request $request)
    {
        $this->watchlist = $watchlist;
        $this->request = $request;
    }

    public function toArray()
    {
        $user = $this->watchlist->getUser();
        $documentary = $this->watchlist->getDocumentary();

        return [
            'id' => $this->watchlist->getId(),
            'user' => [
                'username' => $user->getUsername(),
                'name' => $user->getName()
            ],
            'documentary' => [
                'type' => $documentary->getType(),
                'title' => $documentary->getTitle(),
                'slug' => $documentary->getSlug(),
                'poster' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPoster(),
                'summary' => $documentary->getSummary()
            ],
            'createdAt' => $this->watchlist->getCreatedAt(),
            'updatedAt' => $this->watchlist->getUpdatedAt()
        ];
    }

    public function toObject(array $data)
    {
        // TODO: Implement toObject() method.
    }
}