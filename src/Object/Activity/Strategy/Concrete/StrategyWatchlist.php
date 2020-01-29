<?php

namespace App\Object\Activity\Strategy\Concrete;

use App\Entity\Activity;
use App\Object\Activity\ActivityChild;
use App\Object\Activity\ActivityParent;
use App\Object\Activity\Data\Data;
use App\Object\Activity\Data\WatchlistData;
use App\Object\Activity\Strategy\StrategyInterface;
use App\Service\DocumentaryService;
use Symfony\Component\HttpFoundation\Request;

class StrategyWatchlist implements StrategyInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @param Request $request
     * @param DocumentaryService $documentaryService
     */
    public function __construct(
        Request $request,
        DocumentaryService $documentaryService)
    {
        $this->request = $request;
        $this->documentaryService = $documentaryService;
    }

    /**
     * @param Activity $activityEntity
     * @return Data
     */
    public function createData(Activity $activityEntity)
    {
        $documentaryId = $activityEntity->getObjectId();
        $documentary = $this->documentaryService->getDocumentaryById($documentaryId);

        $watchlistData = new WatchlistData();
        $watchlistData->setDocumentaryId($documentary->getId());
        $watchlistData->setDocumentaryTitle($documentary->getTitle());
        $watchlistData->setDocumentarySlug($documentary->getSlug());
        $watchlistData->setDocumentarySummary($documentary->getSummary());
        $poster = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPoster();
        $watchlistData->setDocumentaryPoster($poster);

        return $watchlistData;
    }
}