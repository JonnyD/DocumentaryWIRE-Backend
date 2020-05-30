<?php

namespace App\Entity;

use App\Enum\DocumentaryType;
use App\Traits\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SeasonRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @Gedmo\Loggable
 */
class Season
{
    use Timestampable;
    
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
    private $seasonNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank()
     */
    private $summary;

    /**
     * @var ArrayCollection|Episode[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Episode", mappedBy="season")
     */
    private $episodes;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
    }

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
    public function getSeasonNumber(): ?int
    {
        return $this->seasonNumber;
    }

    /**
     * @param int $seasonNumber
     */
    public function setSeasonNumber(int $seasonNumber): void
    {
        $this->seasonNumber = $seasonNumber;
    }

    /**
     * @return string
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary(string $summary): void
    {
        $this->summary = $summary;
    }

    /**
     * @return Episode[]|ArrayCollection
     */
    public function getEpisodes()
    {
        return $this->episodes;
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
        $this->episodes->add($episode);
    }

    /**
     * @param $episodes
     */
    public function setDocumentaries($episodes)
    {
        $this->episodes->clear();

        foreach($episodes as $episode) {
            if (!$this->hasEpisode($episode)) {
                $this->addEpisode($episode);
            }
        }
    }
}