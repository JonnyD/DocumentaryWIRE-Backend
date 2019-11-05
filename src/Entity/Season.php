<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SeasonRepository")
 */
class Season
{
    /**
     * @var integer
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @var Episodic
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Episodic", inversedBy="seasons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $episodic;

    /**
     * @var Episode[] | ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Documentary", mappedBy="season")
     */
    private $episodes;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return Season
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return Episodic
     */
    public function getEpisodic(): ?Episodic
    {
        return $this->episodic;
    }

    /**
     * @param Episodic $episodic
     * @return Season
     */
    public function setDocumentary(?Episodic $episodic): self
    {
        $this->episodic = $episodic;

        return $this;
    }

    /**
     * @return ArrayCollection|Episode[]
     */
    public function getEpisodes(): ArrayCollection
    {
        return $this->episodes;
    }

    /**
     * @param Episode $episode
     * @return Season
     */
    public function addEpisode(Episode $episode): self
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setSeason($this);
        }

        return $this;
    }

    /**
     * @param Episode $episode
     * @return Season
     */
    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            // set the owning side to null (unless already changed)
            if ($episode->getSeason() === $this) {
                $episode->setSeason(null);
            }
        }

        return $this;
    }
}
