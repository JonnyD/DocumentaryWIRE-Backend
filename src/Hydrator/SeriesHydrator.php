<?php

namespace App\Hydrator;

use App\Entity\Documentary;
use Symfony\Component\HttpFoundation\Request;

class SeriesHydrator implements HydratorInterface
{
    /**
     * @var Documentary
     */
    private $documentary;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Documentary $documentary
     * @param Request $request
     */
    public function __construct(
        Documentary $documentary,
        Request $request)
    {
        $this->documentary = $documentary;
        $this->request = $request;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->documentary->getId(),
            'type' => $this->documentary->getType(),
            'title' => $this->documentary->getTitle(),
            'slug' => $this->documentary->getSlug(),
            'storyline' => $this->documentary->getStoryline(),
            'summary' => $this->documentary->getSummary(),
            'status' => $this->documentary->getStatus(),
            'views' => $this->documentary->getViews(),
            'shortUrl' => $this->documentary->getShortUrl(),
            'featured' => $this->documentary->getFeatured(),
            'imdbId' => $this->documentary->getImdbId(),
            'yearFrom' => $this->documentary->getYearFrom(),
            'yearTo' => $this->documentary->getYearTo(),
            'poster' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . $this->documentary->getPosterImagePath(),
            'wideImage' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . $this->documentary->getWideImagePath(),
            'category' => [
                'id' => $this->documentary->getCategory()->getId(),
                'name' => $this->documentary->getCategory()->getName()
            ],
            'createdAt' => $this->documentary->getCreatedAt(),
            'updatedAt' => $this->documentary->getUpdatedAt()
        ];

        /**
        if ($documentary->getDocumentaryVideoSources() != null) {
        $videoSources = [];

        foreach ($documentary->getDocumentaryVideoSources() as $documentaryVideoSource) {
        $videoSources[] = $documentaryVideoSource->getVideoSource()->getName();
        }

        $array['videoSources'] = $videoSources;
        }
         **/

        if ($this->documentary->getAddedBy() != null) {
            $array['addedBy'] = [
                'username' => $this->documentary->getAddedBy()->getUsername()
            ];
        }

        $seasonsArray = [];
        foreach ($this->documentary->getChildren() as $child) {
            $episode = $child->getEpisode();

            $episodesArray = [];
            $episodesArray[] = [
                'id' => $child->getId(),
                'number' => $episode->getEpisodeNumber(),
                'title' => $child->getTitle(),
                'imdbId' => $child->getImdbId(),
                'storyline' => $child->getStoryline(),
                'summary' => $child->getSummary(),
                'duration' => $child->getLength(),
                'yearFrom' => $child->getYearFrom(),
                'videoSource' => $episode->getVideoSource()->getName(),
                'videoId' => $episode->getVideoId(),
                'thumbnail' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . $child->getPosterImagePath(),
            ];

            $season = $episode->getSeason();
            $seasonArray = [
                'id' => $season->getId(),
                'number' => $season->getSeasonNumber(),
                'seasonSummary' => $season->getSummary(),
                'episodes' => $episodesArray
            ];

            $seasonsArray[] = $seasonArray;
        }

        $array['series'] = [
            'seasons' => $seasonsArray
        ];

        return $array;
    }
}