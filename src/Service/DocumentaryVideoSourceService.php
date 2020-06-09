<?php

namespace App\Service;

use App\Entity\Documentary;
use App\Entity\DocumentaryVideoSource;
use App\Entity\Episode;
use App\Enum\Sync;
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

    /**
     * @return DocumentaryVideoSource[]
     */
    public function getAllDocumentaryVideoSources()
    {
        return $this->documentaryVideoSourceRepository->findAll();
    }

    public function updateCreatedAtForDocumentaryVideoSources()
    {
        $documentaryVideoSources = $this->getAllDocumentaryVideoSources();

        $chunkSize = 100;
        $chunks = array_chunk($documentaryVideoSources, $chunkSize, true);

        foreach ($chunks as $chunkDocumentaryVideoSources) {
            $editedDocumentaryVideoSources = [];

            /** @var DocumentaryVideoSource[] $chunkDocumentaryVideoSources */
            foreach ($chunkDocumentaryVideoSources as $documentaryVideoSource) {
                $documentaryCreatedAt = $documentaryVideoSource->getDocumentary()->getCreatedAt();
                $documentaryVideoSource->setCreatedAt($documentaryCreatedAt);

                $editedDocumentaryVideoSources[] = $documentaryVideoSource;
            }

            if (count($editedDocumentaryVideoSources) > 0) {
                $this->saveAll($editedDocumentaryVideoSources);
            }
        }
    }

    public function addDocumentaryVideoSourcesFromMovieDocumentary($movie, Documentary $documentary)
    {
        if (!$documentary->isMovie()) {
            return new \Exception();
        }

        $currentDocumentaryVideoSources = $documentary->getDocumentaryVideoSources();
        foreach ($currentDocumentaryVideoSources as $currentDocumentaryVideoSource) {
            $this->remove($currentDocumentaryVideoSource);
        }

        $videoSourceId = $movie['videoSource'];
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
    public function remove(DocumentaryVideoSource $documentaryVideoSource, string $sync = Sync::YES)
    {
        $this->documentaryVideoSourceRepository->remove($documentaryVideoSource, $sync);
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(DocumentaryVideoSource $documentaryVideoSource, string $sync = Sync::YES)
    {
        if ($documentaryVideoSource->getCreatedAt() == null) {
            $documentaryVideoSource->setCreatedAt(new \DateTime());
        } else {
            $documentaryVideoSource->setUpdatedAt(new \DateTime());
        }

        $this->documentaryVideoSourceRepository->save($documentaryVideoSource, $sync);
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveButDontUpdateTimestamps(DocumentaryVideoSource $documentaryVideoSource, string $sync = Sync::YES)
    {
        $this->documentaryVideoSourceRepository->save($documentaryVideoSource, $sync);
    }

    /**
     * @param DocumentaryVideoSource[] $documentaryVideoSources
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveAll($documentaryVideoSources)
    {
        foreach ($documentaryVideoSources as $documentaryVideoSource) {
            $this->saveButDontUpdateTimestamps($documentaryVideoSource, Sync::NO);
        }

        $this->documentaryVideoSourceRepository->flush();
    }
}