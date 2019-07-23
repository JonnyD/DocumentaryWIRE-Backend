<?php

namespace App\Service;

use App\Criteria\VideoSourceCriteria;
use App\Entity\VideoSource;
use App\Repository\VideoSourceRepository;
use Doctrine\Common\Collections\ArrayCollection;

class VideoSourceService
{
    /**
     * @var VideoSourceRepository
     */
    private $videoSourceRepository;

    /**
     * @param VideoSourceRepository $videoSourceRepository
     */
    public function __construct(VideoSourceRepository $videoSourceRepository)
    {
        $this->videoSourceRepository = $videoSourceRepository;
    }

    /**
     * @return VideoSource[]
     */
    public function getAllVideoSources()
    {
        return $this->videoSourceRepository->findAll();
    }

    /**
     * @param VideoSourceCriteria $criteria
     * @return VideoSource[]|ArrayCollection
     */
    public function getAllVideoSourcesByCriteria(VideoSourceCriteria $criteria)
    {
        return $this->videoSourceRepository->findVideoSourcesByCriteria($criteria);
    }
}