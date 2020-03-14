<?php

namespace App\Entity;

use App\Enum\DocumentaryType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EpisodeRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @Gedmo\Loggable
 */
class Episode
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="text", nullable=false)
     *
     * @Assert\NotBlank
     */
    private $episodeNumber;

    /**
     * @var VideoSource
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VideoSource", inversedBy="episodes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $videoSource;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $videoId;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Documentary", inversedBy="episode")
     * @ORM\JoinColumn(name="documentary_id", referencedColumnName="id")
     */
    private $documentary;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Season", inversedBy="episodes")
     * @ORM\JoinColumn(name="season_id", referencedColumnName="id")
     * @Gedmo\Versioned
     */
    private $season;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getEpisodeNumber(): ?int
    {
        return $this->episodeNumber;
    }

    /**
     * @param int $episodeNumber
     */
    public function setEpisodeNumber(int $episodeNumber)
    {
        $this->episodeNumber = $episodeNumber;
    }


    /**
     * @return VideoSource
     */
    public function getVideoSource(): ?VideoSource
    {
        return $this->videoSource;
    }

    /**
     * @param VideoSource $videoSource
     */
    public function setVideoSource(VideoSource $videoSource): void
    {
        $this->videoSource = $videoSource;
    }

    /**
     * @return string
     */
    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    /**
     * @param string $videoId
     */
    public function setVideoId(string $videoId): void
    {
        $this->videoId = $videoId;
    }

    /**
     * @return Documentary|null
     */
    public function getDocumentary(): ?Documentary
    {
        return $this->documentary;
    }

    /**
     * @param Documentary $documentary
     */
    public function setDocumentary(Documentary $documentary)
    {
        $this->documentary = $documentary;
    }

    /**
     * @return Season
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * @param Season $season
     */
    public function setSeason($season): void
    {
        $this->season = $season;
    }
}