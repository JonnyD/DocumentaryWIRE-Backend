<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Traits\Sluggable;
use App\Traits\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\Blameable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @Gedmo\Loggable
 */
class Category
{
    use Timestampable;
    use Blameable;
    use Sluggable;
    use SoftDeleteable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Documentary", mappedBy="category")
     */
    private $documentaries;

    public function __construct()
    {
        $this->documentaries = new ArrayCollection();
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

    public function getCount(): ?int
    {
        return $this->documentaries->count();
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
            $documentary->setCategory($this);
        }

        return $this;
    }

    public function removeDocumentary(Documentary $documentary): self
    {
        if ($this->documentaries->contains($documentary)) {
            $this->documentaries->removeElement($documentary);
            // set the owning side to null (unless already changed)
            if ($documentary->getCategory() === $this) {
                $documentary->setCategory(null);
            }
        }

        return $this;
    }
}
