<?php

namespace App\Service;

use App\Entity\Documentary;
use App\Entity\DocumentaryVideoSource;
use App\Repository\DocumentaryVideoSourceRepository;

class DocumentaryVideoSourceService
{
    /**
     * @var DocumentaryVideoSourceRepository
     */
    private $documentaryVideoSourceRepository;

    /**
     * @var VideoSourceService
     */
    private $videoSourceService;

    /**
     * @param DocumentaryVideoSourceRepository $documentaryVideoSourceRepository
     * @param VideoSourceService $videoSourceService
     */
    public function __construct(
        DocumentaryVideoSourceRepository $documentaryVideoSourceRepository,
        VideoSourceService $videoSourceService)
    {
        $this->documentaryVideoSourceRepository = $documentaryVideoSourceRepository;
        $this->videoSourceService = $videoSourceService;
    }

    public function addDocumentaryVideoSourcesFromStandaloneDocumentary($standalone, Documentary $documentary)
    {
        if (!$documentary->isStandalone()) {
            return new \Exception();
        }

        $currentDocumentaryVideoSources = $documentary->getDocumentaryVideoSources();
        foreach ($currentDocumentaryVideoSources as $currentDocumentaryVideoSource) {
            $this->remove($currentDocumentaryVideoSource);
        }

        $videoSourceId = $standalone['videoSource'];
        $videoSource = $this->videoSourceService->getVideoSourceById($videoSourceId);

        $documentaryVideoSource = new DocumentaryVideoSource();
        $documentaryVideoSource->setDocumentary($documentary);
        $documentaryVideoSource->setVideoSource($videoSource);

        $documentaryVideoSources = [];
        $documentaryVideoSources[] = $documentaryVideoSource;

        return $documentaryVideoSources;
    }

    /**
     * @param $seasons
     * @param Documentary $documentary
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function addDocumentaryVideoSourcesFromEpisodicDocumentary($seasons, Documentary $documentary)
    {
        if (!$documentary->isEpisodic()) {
            throw new \Exception();
        }

        $currentDocumentaryVideoSources = $documentary->getDocumentaryVideoSources();
        foreach ($currentDocumentaryVideoSources as $currentDocumentaryVideoSource) {
            $this->remove($currentDocumentaryVideoSource);
        }

        $videoSourceIds = [];
        foreach ($seasons as $season) {
            $episodes = $season['episodes'];

            foreach ($episodes as $episode) {
                $videoSourceId = $episode['videoSource'];

                $hasVideoSourceId = in_array($videoSourceId, $videoSourceIds);
                if (!$hasVideoSourceId) {
                    $videoSourceIds[] = $videoSourceId;
                }
            }
        }

        $documentaryVideoSources = [];
        foreach ($videoSourceIds as $videoSourceId) {
            $videoSource = $this->videoSourceService->getVideoSourceById($videoSourceId);

            $documentaryVideoSource = new DocumentaryVideoSource();
            $documentaryVideoSource->setVideoSource($videoSource);
            $documentaryVideoSource->setDocumentary($documentary);

            $documentaryVideoSources[] = $documentaryVideoSource;
        }

        return $documentaryVideoSources;
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(DocumentaryVideoSource $documentaryVideoSource, bool $sync = true)
    {
        $this->documentaryVideoSourceRepository->remove($documentaryVideoSource, $sync);
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(DocumentaryVideoSource $documentaryVideoSource, $sync = true)
    {
        $this->documentaryVideoSourceRepository->save($documentaryVideoSource, $sync);
    }
}