<?php

namespace App\Object\Activity\Strategy\Concrete;

use App\Entity\Activity;
use App\Object\Activity\ActivityChild;
use App\Object\Activity\ActivityParent;
use App\Object\Activity\Data\WatchlistData;
use App\Object\Activity\Strategy\StrategyInterface;
use App\Service\DocumentaryService;

class StrategyWatchlist implements StrategyInterface
{
    /**
     * @var int
     */
    private $groupNumber;

    /**
     * @var int
     */
    private $previousGroupNumber;

    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @param int $groupNumber
     * @param int $previousGroupNumber
     * @param DocumentaryService $documentaryService
     */
    public function __construct(
        int $groupNumber,
        int $previousGroupNumber,
        DocumentaryService $documentaryService)
    {
        $this->groupNumber = $groupNumber;
        $this->previousGroupNumber = $previousGroupNumber;
        $this->documentaryService = $documentaryService;
    }

    /**
     * @param Activity $activityEntity
     * @return array
     */
    public function createActivity(Activity $activityEntity)
    {
        $documentaryId = $activityEntity->getObjectId();

        $user = $activityEntity->getUser();
        $name = $user->getName();
        $avatar = $user->getAvatar();
        $username = $user->getUsername();

        $documentary = $this->documentaryService->getDocumentaryById($documentaryId);

        $watchlistData = new WatchlistData();
        $watchlistData->setDocumentaryId($documentary->getId());
        $watchlistData->setDocumentaryTitle($documentary->getTitle());
        $watchlistData->setDocumentarySlug($documentary->getSlug());
        $watchlistData->setDocumentarySummary($documentary->getSummary());
        //$poster = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPoster();
        //$watchlistData->setDocumentaryPoster($poster);

        $tempActivityArray = [];
        if ($this->groupNumber != $this->previousGroupNumber) {
            $parent = new ActivityParent();
            $parent->setData($watchlistData);
            $parent->setName($name);
            $parent->setAvatar($avatar);
            $parent->setUsername($username);

            $tempActivityArray['parent'] = $parent->toArray();
        } else {
            $child = new ActivityChild();
            $child->setData($watchlistData);
            $child->setName($name);
            $child->setAvatar($avatar);
            $child->setUsername($username);

            $tempActivityArray['child'][] = $child->toArray();
        }

        return $tempActivityArray;
    }
}