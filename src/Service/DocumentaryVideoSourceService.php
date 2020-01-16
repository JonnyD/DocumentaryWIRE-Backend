<?php

namespace App\Service;

use App\Entity\Documentary;
use App\Entity\DocumentaryVideoSource;
use App\Entity\Episode;
use App\Entity\Season;
use App\Repository\DocumentaryVideoSourceRepository;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @param ArrayCollection | Season[] $seasons
     * @param Documentary $documentary
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function addDocumentaryVideoSourcesFroSeriesDocumentary(array $seasons, Documentary $documentary)
    {
        if (!$documentary->isSeries()) {
            throw new \Exception();
        }

        $currentDocumentaryVideoSources = $documentary->getDocumentaryVideoSources();
        foreach ($currentDocumentaryVideoSources as $currentDocumentaryVideoSource) {
            $this->remove($currentDocumentaryVideoSource);
        }

        $videoSourceIds = [];

        foreach ($seasons as $season) {
            $episodes = $season->getEpisodes()->toArray();

            /** @var Episode $episode */
            foreach ($episodes as $episode) {
                $videoSourceId = $episode->getVideoSource()->getId();

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