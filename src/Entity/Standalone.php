<?php

namespace App\Entity;

use App\Enum\DocumentaryType;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StandaloneRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @Gedmo\Loggable
 */
class Standalone extends Documentary
{

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

    public function __construct()
    {
        parent::__construct();
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
     * @return Standalone
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
     * @return Standalone
     */
    public function setVideoSource(?VideoSource $videoSource): self
    {
        $this->videoSource = $videoSource;

        return $this;
    }

}