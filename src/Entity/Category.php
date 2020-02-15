<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Enum\CategoryStatus;
use App\Traits\Sluggable;
use App\Traits\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\Blameable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiProperty;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(fields={"slug"})
 * @Gedmo\Loggable
 * @ExclusionPolicy("all")
 */
class Category
{
    use Timestampable;
    use Blameable;
    use SoftDeleteable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=5,
     *     max="50",
     *     minMessage="The name must be longer than 5 characters"
     * )
     * @Gedmo\Versioned
     * @Expose
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Gedmo\Slug(fields={"name"})
     * @Assert\NotBlank()
     * @Gedmo\Versioned
     * @Expose
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Documentary", mappedBy="category")
     *
     * @var ArrayCollection|Documentary[]
     */
    private $documentaries;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $documentaryCount;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $status;

    public function __construct()
    {
        $this->documentaries = new ArrayCollection();
        $this->documentaryCount = 0;
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

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * @Groups({"category:read"})
     *
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count)
    {
        $this->count = $count;
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

    /**
     * @return int
     */
    public function getDocumentaryCount()
    {
        $count = 0;

        foreach ($this->documentaries as $documentary) {
            if ($documentary->isPublished()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return ($this->status === CategoryStatus::ENABLED);
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return ($this->status === CategoryStatus::DISABLED);
    }
}
