<?php

namespace App\Hydrator;

use App\Entity\Documentary;
use Symfony\Component\HttpFoundation\Request;

class EpisodeHydrator implements HydratorInterface
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
            'year' => $this->documentary->getYearFrom(),
            'length' => $this->documentary->getLength(),
            'status' => $this->documentary->getStatus(),
            'views' => $this->documentary->getViews(),
            'shortUrl' => $this->documentary->getShortUrl(),
            'featured' => $this->documentary->getFeatured(),
            'imdbId' => $this->documentary->getImdbId(),
            'createdAt' => $this->documentary->getCreatedAt(),
            'updatedAt' => $this->documentary->getUpdatedAt(),
            'commentCount' => $this->documentary->getCommentCount()
        ];

        if ($this->documentary->getPoster() != null) {
            $array['poster'] = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $this->documentary->getPoster();
        } else {
            $array['poster'] = null;
        }

        if ($this->documentary->getWideImage() != null) {
            $array['wideImage'] = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/wide/' . $this->documentary->getWideImage();
        } else {
            $array['wideImage'] = null;
        }

        if ($this->documentary->getAddedBy() != null) {
            $array['addedBy'] = [
                'username' => $this->documentary->getAddedBy()->getUsername()
            ];
        }

        /**
         * @TODO
        if ($documentary->getDocumentaryVideoSources() != null) {
        $videoSources = [];

        foreach ($documentary->getDocumentaryVideoSources() as $documentaryVideoSource) {
        $videoSources[] = $documentaryVideoSource->getVideoSource()->getName();
        }

        $serialized['videoSources'] = $videoSources;
        } **/

        $episode = $this->documentary->getEpisode();

        if ($episode->getVideoSource() != null) {
            $array['movie']['videoSource'] = [
                'id' => $episode->getVideoSource()->getId(),
                'name' => $episode->getVideoSource()->getName()
            ];
        }

        if ($episode->getVideoId() != null) {
            $array['movie']['videoId'] = $episode->getVideoId();
        }

        return $array;
    }
}