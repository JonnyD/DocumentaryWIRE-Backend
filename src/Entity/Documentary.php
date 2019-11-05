<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Enum\DocumentaryType;
use App\Traits\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\Blameable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use App\Traits\Sluggable;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"standalone" = "App\Entity\Standalone", "episodic" = "App\Entity\Episodic", "episode" = "App\Entity\Episode"})
 * @ORM\HasLifecycleCallbacks
 *
 * @Gedmo\Loggable
 */
abstract class Documentary
{
    use Timestampable;
    use Blameable;
    use SoftDeleteable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"documentary:write", "documentary:read"})
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Gedmo\Slug(fields={"title"})
     */
    private $slug;

    /**
     * @ORM\Column(type="text")
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    private $storyline;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    private $summary;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    private $year;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $length;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\Versioned
     */
    private $views;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $shortUrl;


    /**
     * @ORM\Column(type="boolean")
     * @Gedmo\Versioned
     */
    private $featured;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    private $posterFileName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $wideImage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $imdbId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="documentary")
     * @ORM\JoinColumn(nullable=false)
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="documentary")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Watchlist", mappedBy="documentary")
     */
    private $watchlists;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="documentaries")
     *
     * @var User
     */
    private $addedBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Season", inversedBy="episodes")
     */
    private $season;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->watchlists = new ArrayCollection();
        $this->featured = false;
        $this->views = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getStoryline(): ?string
    {
        return $this->storyline;
    }

    public function setStoryline(string $storyline): self
    {
        $this->storyline = $storyline;

        return $this;
    }

    public function getSummary(): ?string
    {
        $returnString = null;

        if ($this->summary === null) {
            if (strlen($this->storyline) === 200) {
                $returnString = $this->storyline;
            } else {
                $returnString = substr($this->storyline, 0, 200) . "...";
            }
        } else {
            $returnString = $this->summary;
        }

        return $returnString;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

        return $this;
    }

    public function getShortUrl(): ?string
    {
        return $this->shortUrl;
    }

    public function setShortUrl(?string $shortUrl): self
    {
        $this->shortUrl = $shortUrl;

        return $this;
    }



    public function getFeatured(): ?bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;

        return $this;
    }

    public function getWideImage(): ?string
    {
        return $this->wideImage;
    }

    public function setWideImage(?string $wideImage): self
    {
        $this->wideImage = $wideImage;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isStandalone(): bool
    {
        return ($this->type === DocumentaryType::STANDALONE);
    }

    /**
     * @return bool
     */
    public function isEpisodic(): bool
    {
        return ($this->type === DocumentaryType::EPISODIC);
    }

    /**
     * @return string
     */
    public function getImdbId(): ?string
    {
        return $this->imdbId;
    }

    /**
     * @param string $imdbId
     */
    public function setImdbId($imdbId): void
    {
        $this->imdbId = $imdbId;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     * @return Documentary
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setDocumentary($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getDocumentary() === $this) {
                $comment->setDocumentary(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Watchlist[]
     */
    public function getWatchlists(): Collection
    {
        return $this->watchlists;
    }

    public function addWatchlist(Watchlist $watchlist): self
    {
        if (!$this->watchlists->contains($watchlist)) {
            $this->watchlists[] = $watchlist;
            $watchlist->setDocumentary($this);
        }

        return $this;
    }

    public function removeWatchlist(Watchlist $watchlist): self
    {
        if ($this->watchlists->contains($watchlist)) {
            $this->watchlists->removeElement($watchlist);
            // set the owning side to null (unless already changed)
            if ($watchlist->getDocumentary() === $this) {
                $watchlist->setDocumentary(null);
            }
        }

        return $this;
    }

    public function getCommentCount(): ?int
    {
        return $this->comments->count();
    }

    public function serialize()
    {
        return serialize(array(
            $this->title
        ));
    }

    /**
     * @return mixed
     */
    public function getPosterFileName()
    {
        return $this->posterFileName;
    }

    /**
     * @param string $posterFileName
     */
    public function setPosterFileName(string $posterFileName)
    {
        $this->posterFileName = $posterFileName;
    }

    /**
     * @return string
     */
    public function getPosterImagePath()
    {
        return 'uploads/documentary/posters/'.$this->getPosterFileName();
    }

    /**
     * @return string
     */
    public function getWideImagePath()
    {
        return 'uploads/documentary/wide/'.$this->getWideImage();
    }

    /**
     * @return User|null
     */
    public function getAddedBy(): ?User
    {
        return $this->addedBy;
    }

    /**
     * @param User $user
     */
    public function setAddedBy(User $user)
    {
        $this->addedBy = $user;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): self
    {
        $this->season = $season;

        return $this;
    }
}
