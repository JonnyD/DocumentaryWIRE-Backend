<?php

namespace App\Object\Activity\Strategy\Concrete;

use App\Entity\Activity;
use App\Object\Activity\ActivityChild;
use App\Object\Activity\ActivityParent;
use App\Object\Activity\Data\AddedData;
use App\Object\Activity\Data\JoinedData;
use App\Object\Activity\Data\WatchlistData;
use App\Object\Activity\Strategy\StrategyInterface;
use App\Service\DocumentaryService;

class StrategyAdded implements StrategyInterface
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

        $addedData = new AddedData();
        $addedData->setDocumentaryId($documentary->getId());
        $addedData->setDocumentaryTitle($documentary->getTitle());
        $addedData->setDocumentarySlug($documentary->getSlug());
        $addedData->setDocumentarySummary($documentary->getSummary());
        $addedData->setDocumentaryPoster($documentary->getPoster());

        $tempActivityArray = [];
        if ($this->groupNumber != $this->previousGroupNumber) {
            $parent = new ActivityParent();
            $parent->setData($addedData);
            $parent->setName($name);
            $parent->setAvatar($avatar);
            $parent->setUsername($username);

            $tempActivityArray['parent'] = $parent->toArray();
        } else {
            $child = new ActivityChild();
            $child->setData($addedData);
            $child->setName($name);
            $child->setAvatar($avatar);
            $child->setUsername($username);

            $tempActivityArray['child'][] = $child->toArray();
        }

        return $tempActivityArray;
    }
}