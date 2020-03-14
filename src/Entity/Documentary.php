<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Enum\DocumentaryStatus;
use App\Enum\DocumentaryType;
use App\Enum\YesNo;
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
use phpDocumentor\Reflection\Types\Parent_;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DocumentaryRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @Gedmo\Loggable
 */
class Documentary
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
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Gedmo\Versioned
     */
    private $todayViews;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Versioned
     */
    private $viewsDate;

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
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Gedmo\Versioned
     *
     * @Assert\NotBlank
     */
    private $yearFrom;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $yearTo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $poster;

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
     * @var Movie
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Movie", mappedBy="documentary", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $movie;

    /**
     * @var Series
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Series", mappedBy="documentary", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $series;

    /**
     * @var Episode
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Episode", mappedBy="documentary", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $episode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $imdbId;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\Versioned
     */
    private $commentCount;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\Versioned
     */
    private $watchlistCount;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Gedmo\Versioned
     */
    private $isParent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="documentary")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Versioned
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
     * @var DocumentaryVideoSource[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\DocumentaryVideoSource", mappedBy="documentary", cascade={"persist"})
     */
    private $documentaryVideoSources;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="documentaries")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var User
     */
    private $addedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Documentary", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Documentary", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->watchlists = new ArrayCollection();
        $this->documentaryVideoSources = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->featured = false;
        $this->views = 0;
        $this->todayViews = 0;
        $this->viewsDate = new \DateTime();
        $this->commentCount = 0;
        $this->watchlistCount = 0;
        $this->isParent = YesNo::NO;
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

    /**
     * @return bool
     */
    public function isPublished()
    {
        return ($this->status === DocumentaryStatus::PUBLISH);
    }

    /**
     * @return bool
     */
    public function isDraft()
    {
        return ($this->status === DocumentaryStatus::DRAFT);
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return ($this->status === DocumentaryStatus::PENDING);
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

    public function incrementViews()
    {
        $this->views++;
    }

    /**
     * @return int
     */
    public function getTodayViews(): int
    {
        return $this->todayViews;
    }

    /**
     * @param int $todayViews
     */
    public function setTodayViews(int $todayViews): void
    {
        $this->todayViews = $todayViews;
    }

    public function incrementTodayViews()
    {
        $this->todayViews++;
    }

    /**
     * @return string
     */
    public function isParent()
    {
        return $this->isParent;
    }

    /**
     * @param string $isParent
     */
    public function setIsParent(string $isParent)
    {
        $this->isParent = $isParent;
    }

    /**
     * @return \DateTime
     */
    public function getViewsDate(): ?\DateTime
    {
        return $this->viewsDate;
    }

    /**
     * @param \DateTime $viewsDate
     */
    public function setViewsDate(\DateTime $viewsDate): void
    {
        $this->viewsDate = $viewsDate;
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
     * @return Movie|null
     */
    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    /**
     * @param Movie $movie
     */
    public function setMovie(Movie $movie = null)
    {
        $this->movie = $movie;
        if ($movie != null) {
            $movie->setDocumentary($this);
        }
    }

    /**
     * @return Series|null
     */
    public function getSeries(): ?Series
    {
        return $this->series;
    }

    /**
     * @param Series $series
     */
    public function setSeries(Series $series)
    {
        $this->series = $series;
        $series->setDocumentary($this);
    }

    /**
     * @return Episode|null
     */
    public function getEpisode(): ?Episode
    {
        return $this->episode;
    }

    /**
     * @param Episode $episode
     */
    public function setEpisode(Episode $episode)
    {
        $this->episode = $episode;
        $episode->setDocumentary($this);
    }

    /**
     * @return bool
     */
    public function isMovie(): bool
    {
        return ($this->type === DocumentaryType::MOVIE);
    }

    /**
     * @return bool
     */
    public function isSeries(): bool
    {
        return ($this->type === DocumentaryType::SERIES);
    }

    /**
     * @return bool
     */
    public function isEpisode(): bool
    {
        return ($this->type === DocumentaryType::EPISODE);
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

    /**
     * @return Collection|DocumentaryVideoSource[]
     */
    public function getDocumentaryVideoSources(): Collection
    {
        return $this->documentaryVideoSources;
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @return Documentary
     */
    public function addDocumentaryVideoSource(DocumentaryVideoSource $documentaryVideoSource): self
    {
        if (!$this->documentaryVideoSources->contains($documentaryVideoSource)) {
            $this->documentaryVideoSources[] = $documentaryVideoSource;
            $documentaryVideoSource->setDocumentary($this);
        }

        return $this;
    }

    /**
     * @param DocumentaryVideoSource $documentaryVideoSource
     * @return Documentary
     */
    public function removeDocumentaryVideoSource(DocumentaryVideoSource $documentaryVideoSource): self
    {
        if ($this->documentaryVideoSources->contains($documentaryVideoSource)) {
            $this->documentaryVideoSources->removeElement($documentaryVideoSource);
            // set the owning side to null (unless already changed)
            if ($documentaryVideoSource->getDocumentary() === $this) {
                $documentaryVideoSource->setDocumentary(null);
            }
        }

        return $this;
    }

    /**
     * @param array $documentaryVideoSources
     */
    public function setDocumentaryVideoSources(array $documentaryVideoSources) {
        $this->documentaryVideoSources = new ArrayCollection();

        foreach ($documentaryVideoSources as $documentaryVideoSource) {
            $this->addDocumentaryVideoSource($documentaryVideoSource);
        }
    }

    /**
     * @return int|null
     */
    public function getCommentCount(): ?int
    {
        return $this->commentCount;
    }

    /**
     * @param int $commentCount
     */
    public function setCommentCount(int $commentCount)
    {
        $this->commentCount = $commentCount;
    }

    /**
     * @return int
     */
    public function getWatchlistCount()
    {
        return $this->watchlistCount;
    }

    /**
     * @param int $watchlistCount
     */
    public function setWatchlistCount(int $watchlistCount)
    {
        $this->watchlistCount = $watchlistCount;
    }

    public function serialize()
    {
        return serialize(array(
            $this->title
        ));
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
    public function setYearFrom(int $yearFrom = null): void
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
     * @return mixed
     */
    public function getPoster()
    {
        return $this->poster;
    }

    /**
     * @param string $poster
     */
    public function setPoster(string $poster)
    {
        $this->poster = $poster;
    }

    /**
     * @return string
     */
    public function getPosterImagePath()
    {
        return '/uploads/posters/'.$this->getPoster();
    }

    /**
     * @return string
     */
    public function getWideImagePath()
    {
        return '/uploads/wide/'.$this->getWideImage();
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

    /**
     * @param Documentary $documentary
     * @return bool
     */
    public function hasChild(Documentary $documentary)
    {
        return $this->children->contains($documentary);
    }

    /**
     * @param Documentary $documentary
     */
    public function addChild(Documentary $documentary)
    {
        if (!$this->hasChild($documentary)) {
            $this->children->add($documentary);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ArrayCollection|Documentary[] $children
     */
    public function setChildren($children)
    {
        $this->children->clear();

        foreach ($children as $child) {
            $this->addChild($child);
            $child->setParent($this);
        }
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Documentary $parent
     */
    public function setParent(Documentary $parent)
    {
        $this->parent = $parent;
    }
}
