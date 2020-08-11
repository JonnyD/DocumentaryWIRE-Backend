<?php

namespace App\Hydrator;

use App\Entity\VideoSource;

class VideoSourceHydrator implements HydratorInterface
{
    /**
     * @var VideoSource
     */
    private $videoSource;

    /**
     * @param VideoSource $videoSource
     */
    public function __construct(
        VideoSource $videoSource)
    {
        $this->videoSource = $videoSource;
    }

    public function toArray()
    {
        return [
            'id' => $this->videoSource->getId(),
            'name' => $this->videoSource->getName(),
            'embedAllowed' => $this->videoSource->getEmbedAllowed(),
            'embedCode' => $this->videoSource->getEmbedCode(),
            'status' => $this->videoSource->getStatus()
        ];
    }

    public function toObject(array $data)
    {
        // TODO: Implement toObject() method.
    }
}