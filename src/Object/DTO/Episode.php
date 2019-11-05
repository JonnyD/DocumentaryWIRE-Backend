<?php

namespace App\Object\DTO;

class Episode
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $duration;

    /**
     * @var string
     */
    private $plot;

    /**
     * @var string
     */
    private $thumbnail;

    /**
     * @var string
     */
    private $imdbID;

    /**
     * @var float
     */
    private $imdbRating;

    /**
     * @var int
     */
    private $imdbVotes;

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
    public function setNumber(int $number): void
    {
        $this->number = $number;
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
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return string
     */
    public function getPlot(): ?string
    {
        return $this->plot;
    }

    /**
     * @param string $plot
     */
    public function setPlot(string $plot = null): void
    {
        $this->plot = $plot;
    }

    /**
     * @return string
     */
    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    /**
     * @param string $thumbnail
     */
    public function setThumbnail(string $thumbnail = null): void
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return string
     */
    public function getImdbID(): string
    {
        return $this->imdbID;
    }

    /**
     * @param string $imdbID
     */
    public function setImdbID(string $imdbID): void
    {
        $this->imdbID = $imdbID;
    }

    /**
     * @return float
     */
    public function getImdbRating(): ?float
    {
        return $this->imdbRating;
    }

    /**
     * @param float $imdbRating
     */
    public function setImdbRating(float $imdbRating = null): void
    {
        $this->imdbRating = $imdbRating;
    }

    /**
     * @return int
     */
    public function getImdbVotes(): ?int
    {
        return $this->imdbVotes;
    }

    /**
     * @param int $imdbVotes
     */
    public function setImdbVotes(int $imdbVotes = null): void
    {
        $this->imdbVotes = $imdbVotes;
    }
}