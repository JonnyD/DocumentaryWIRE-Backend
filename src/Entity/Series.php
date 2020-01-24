<?php

namespace App\Entity;

use App\Enum\DocumentaryType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SeriesRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @Gedmo\Loggable
 */
class Series
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
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    protected $yearFrom;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    protected $yearTo;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Documentary", inversedBy="series")
     * @ORM\JoinColumn(name="documentary_id", referencedColumnName="id")
     */
    private $documentary;

    /**
     * @var ArrayCollection | Season[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Season", mappedBy="series", cascade={"persist"}), fetch="EAGER")
     */
    private $seasons;

    public function __construct()
    {
        $this->seasons = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return ArrayCollection|Season[]
     */
    public function getSeasons(): ArrayCollection
    {
        if ($this->seasons instanceof PersistentCollection) {
            $asArray = $this->seasons->getValues();
            $this->seasons = new ArrayCollection($asArray);
        }
        return $this->seasons;
    }

    public function addSeason(Season $season): self
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setDocumentary($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): self
    {
        if ($this->seasons->contains($season)) {
            $this->seasons->removeElement($season);
            // set the owning side to null (unless already changed)
            if ($season->getEpisodic() === $this) {
                $season->setDocumentary(null);
            }
        }

        return $this;
    }

}