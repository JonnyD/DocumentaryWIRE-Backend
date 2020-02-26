<?php

namespace App\Entity;

use App\Enum\DocumentaryType;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MovieRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @Gedmo\Loggable
 */
class Movie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Documentary", inversedBy="movie")
     * @ORM\JoinColumn(name="documentary_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $documentary;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Versioned
     */
    private $videoId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\VideoSource", inversedBy="documentaries")
     * @ORM\JoinColumn(nullable=true)
     */
    private $videoSource;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

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
     * @return null|string
     */
    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    /**
     * @param string $videoId
     * @return Movie
     */
    public function setVideoId(string $videoId): self
    {
        $this->videoId = $videoId;

        return $this;
    }

    /**
     * @return VideoSource|null
     */
    public function getVideoSource(): ?VideoSource
    {
        return $this->videoSource;
    }

    /**
     * @param VideoSource|null $videoSource
     * @return Movie
     */
    public function setVideoSource(?VideoSource $videoSource): self
    {
        $this->videoSource = $videoSource;

        return $this;
    }

}