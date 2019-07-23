<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VideoSourceRepository")
 */
class VideoSource
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $embed;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $embedCode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Documentary", mappedBy="videoSource")
     */
    private $documentaries;

    public function __construct()
    {
        $this->documentaries = new ArrayCollection();
        $this->embed = false;
        $this->enabled = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmbed(): ?bool
    {
        return $this->embed;
    }

    public function setEmbed(bool $embed): self
    {
        $this->embed = $embed;

        return $this;
    }

    public function getEmbedCode(): ?string
    {
        return $this->embedCode;
    }

    public function setEmbedCode(string $embedCode = null): self
    {
        $this->embedCode = $embedCode;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return Collection|Documentary[]
     */
    public function getDocumentaries(): Collection
    {
        return $this->documentaries;
    }

    public function addDocumentary(Documentary $documentary): self
    {
        if (!$this->documentaries->contains($documentary)) {
            $this->documentaries[] = $documentary;
            $documentary->setVideoSource($this);
        }

        return $this;
    }

    public function removeDocumentary(Documentary $documentary): self
    {
        if ($this->documentaries->contains($documentary)) {
            $this->documentaries->removeElement($documentary);
            // set the owning side to null (unless already changed)
            if ($documentary->getVideoSource() === $this) {
                $documentary->setVideoSource(null);
            }
        }

        return $this;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'embed' => $this->getEmbed(),
            'embed_code' => $this->getEmbedCode(),
            'enabled' => $this->getEnabled()
        ];
    }

}
