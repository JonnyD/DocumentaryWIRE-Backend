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
     * @ORM\Column(type="string")
     */
    private $embedAllowed;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $embedCode;

    /**
     * @ORM\Column(type="string")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Documentary", mappedBy="videoSource")
     */
    private $documentaries;

    public function __construct()
    {
        $this->documentaries = new ArrayCollection();
        $this->embedAllowed = "no";
        $this->status = "disabled";
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

    public function getEmbedAllowed(): ?string
    {
        return $this->embedAllowed;
    }

    public function setEmbedAllowed(string $embedAllowed): self
    {
        $this->embedAllowed = $embedAllowed;

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
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
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
            'embedAllowed' => $this->getEmbedAllowed(),
            'embedCode' => $this->getEmbedCode(),
            'status' => $this->getStatus()
        ];
    }

}
