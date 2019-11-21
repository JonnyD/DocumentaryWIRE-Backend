<?php

namespace App\Entity;

use App\Enum\DocumentaryType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EpisodicRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @Gedmo\Loggable
 */
class Episodic
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Documentary", inversedBy="episodic")
     * @ORM\JoinColumn(name="documentary_id", referencedColumnName="id")
     */
    private $documentary;

    /**
     * @var ArrayCollection | Season[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Season", mappedBy="episodic"), fetch="EAGER")
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