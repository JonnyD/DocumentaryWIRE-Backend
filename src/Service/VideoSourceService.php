<?php

namespace App\Service;

use App\Entity\VideoSource;
use App\Repository\VideoSourceRepository;

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
}