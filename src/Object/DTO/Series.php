<?php

namespace App\Object\DTO;

use Doctrine\Common\Collections\ArrayCollection;

class Series
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $plot;

    /**
     * @var string
     */
    private $poster;

    /**
     * @var int
     */
    private $yearFrom;

    /**
     * @var int
     */
    private $yearTo;

    /**
     * @var string
     */
    private $imdbId;

    /**
     * @var float
     */
    private $imdbRating;

    /**
     * @var int
     */
    private $imdbVotes;

    /**
     * @var ArrayCollection | Season[]
     */
    private $seasons;

    public function __construct()
    {
        $this->seasons = new ArrayCollection();
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getPlot(): string
    {
        return $this->plot;
    }

    /**
     * @param string $plot
     */
    public function setPlot(string $plot): void
    {
        $this->plot = $plot;
    }

    /**
     * @return string
     */
    public function getPoster(): string
    {
        return $this->poster;
    }

    /**
     * @param string $poster
     */
    public function setPoster(string $poster): void
    {
        $this->poster = $poster;
    }

    /**
     * @return int
     */
    public function getYearFrom(): ?int
    {
        return $this->yearFrom;
    }

    /**
     * @param int $yearFrom
     */
    public function setYearFrom(int $yearFrom): void
    {
        $this->yearFrom = $yearFrom;
    }

    /**
     * @return int
     */
    public function getYearTo(): ?int
    {
        return $this->yearTo;
    }

    /**
     * @param int $yearTo
     */
    public function setYearTo(int $yearTo): void
    {
        $this->yearTo = $yearTo;
    }

    /**
     * @return string
     */
    public function getImdbId(): string
    {
        return $this->imdbId;
    }

    /**
     * @param string $imdbId
     */
    public function setImdbId(string $imdbId): void
    {
        $this->imdbId = $imdbId;
    }

    /**
     * @return float
     */
    public function getImdbRating(): float
    {
        return $this->imdbRating;
    }

    /**
     * @param float $imdbRating
     */
    public function setImdbRating(float $imdbRating): void
    {
        $this->imdbRating = $imdbRating;
    }

    /**
     * @return int
     */
    public function getImdbVotes(): int
    {
        return $this->imdbVotes;
    }

    /**
     * @param int $imdbVotes
     */
    public function setImdbVotes(int $imdbVotes): void
    {
        $this->imdbVotes = $imdbVotes;
    }

    /**
     * @param Season $season
     * @return bool
     */
    public function hasSeason(Season $season)
    {
        return $this->seasons->contains($season);
    }

    /**
     * @param Season $season
     */
    public function addSeason(Season $season)
    {
        if (!$this->hasSeason($season)) {
            $this->seasons->add($season);
        }
    }

    /**
     * @return Season[]|ArrayCollection
     */
    public function getSeasons()
    {
        return $this->seasons;
    }

    /**
     * @return int
     */
    public function countSeasons()
    {
        return $this->seasons->count();
    }
}