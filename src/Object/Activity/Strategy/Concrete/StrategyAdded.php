<?php

namespace App\Object\Activity\Strategy\Concrete;

use App\Entity\Activity;
use App\Object\Activity\ActivityChild;
use App\Object\Activity\ActivityParent;
use App\Object\Activity\Data\AddedData;
use App\Object\Activity\Data\Data;
use App\Object\Activity\Data\JoinedData;
use App\Object\Activity\Data\WatchlistData;
use App\Object\Activity\Strategy\StrategyInterface;
use App\Service\DocumentaryService;
use Symfony\Component\HttpFoundation\Request;

class StrategyAdded implements StrategyInterface
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

        $addedData = new AddedData();
        $addedData->setDocumentaryId($documentary->getId());
        $addedData->setDocumentaryTitle($documentary->getTitle());
        $addedData->setDocumentarySlug($documentary->getSlug());
        $addedData->setDocumentarySummary($documentary->getSummary());
        $poster = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPoster();
        $addedData->setDocumentaryPoster($poster);

        return $addedData;
    }
}