<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *    name="documentary_video_source",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="documentary_video_source_unique", columns={"documentary_id", "video_source_id"})
 *    }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ActivityRepository")
 * @ORM\HasLifecycleCallbacks
 */
class DocumentaryVideoSource
{
    use Timestampable;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Documentary
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Documentary", inversedBy="documentaryVideoSources")
     */
    private $documentary;

    /**
     * @var VideoSource
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VideoSource", inversedBy="documentaryVideoSources")
     */
    private $videoSource;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Documentary
     */
    public function getDocumentary(): Documentary
    {
        return $this->documentary;
    }

    /**
     * @param Documentary $documentary
     */
    public function setDocumentary(Documentary $documentary): void
    {
        $this->documentary = $documentary;
    }

    /**
     * @return VideoSource
     */
    public function getVideoSource(): VideoSource
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
}