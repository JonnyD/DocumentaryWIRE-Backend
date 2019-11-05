<?php

namespace App\Object\DTO;

use Doctrine\Common\Collections\ArrayCollection;

class Season
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var ArrayCollection | Episode[]
     */
    private $episodes;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number)
    {
        $this->number = $number;
    }

    /**
     * @param Episode $episode
     * @return bool
     */
    public function hasEpisode(Episode $episode)
    {
        return $this->episodes->contains($episode);
    }

    /**
     * @param Episode $episode
     */
    public function addEpisode(Episode $episode)
    {
        if (!$this->hasEpisode($episode)) {
            $this->episodes->add($episode);
        }
    }

    /**
     * @return Episode[]|ArrayCollection
     */
    public function getEpisodes()
    {
        return $this->episodes;
    }
}