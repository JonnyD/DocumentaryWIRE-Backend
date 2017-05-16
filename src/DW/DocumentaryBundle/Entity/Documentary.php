<?php

namespace DW\DocumentaryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use DW\BaseBundle\Traits\Sluggable;
use DW\CategoryBundle\Entity\Category;
use DW\CommentBundle\Entity\Comment;
use DW\WatchlistBundle\Entity\Watchlist;
use Gedmo\Timestampable\Traits\Timestampable;

class Documentary
{
    use Timestampable;
    use Sluggable;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $storyline;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $length;

    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $views;

    /**
     * @var string
     */
    private $shortUrl;

    /**
     * @var string
     */
    private $poster;

    /**
     * @var string
     */
    private $videoSource;

    /**
     * @var string
     */
    private $videoId;

    /**
     * @var bool
     */
    private $featured;

    /**
     * @var string
     */
    private $featuredImage;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var ArrayCollection|Comment[]
     */
    private $comments;

    /**
     * @var int
     */
    private $commentCount;

    /**
     * @var ArrayCollection|Watchlist[]
     */
    private $watchlisted;

    /**
     * @var int
     */
    private $watchlistCount;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->commentCount = 0;
        $this->watchlisted = new ArrayCollection();
        $this->watchlistCount = 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getStoryline()
    {
        return $this->storyline;
    }

    /**
     * @param string $storyline
     */
    public function setStoryline(string $storyline)
    {
        $this->storyline = $storyline;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        $excerpt = $this->summary;
        if ($excerpt != null || !empty($excerpt)) {
            $excerpt = strip_tags($this->summary);
            $excerpt = substr($excerpt, 0, 160);
        } else {
            $description = strip_tags($this->storyline);
            $excerpt = substr($description, 0, 160);
        }
        return $excerpt;
    }

    /**
     * @param string $summary
     */
    public function setSummary(string $summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year)
    {
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength(int $length)
    {
        $this->length = $length;
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
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param int $views
     */
    public function setViews(int $views)
    {
        $this->views = $views;
    }

    public function incrementViews()
    {
        $this->views++;
    }

    /**
     * @return string
     */
    public function getShortUrl()
    {
        return $this->shortUrl;
    }

    /**
     * @param string $shortUrl
     */
    public function setShortUrl(string $shortUrl)
    {
        $this->shortUrl = $shortUrl;
    }

    /**
     * @return string
     */
    public function getPoster()
    {
        return 'uploads/posters/'.$this->poster;
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
    public function getVideoSource()
    {
        return $this->videoSource;
    }

    /**
     * @param string $videoSource
     */
    public function setVideoSource(string $videoSource)
    {
        $this->videoSource = $videoSource;
    }

    /**
     * @return string
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * @param string $videoId
     */
    public function setVideoId(string $videoId)
    {
        $this->videoId = $videoId;
    }

    /**
     * @return boolean
     */
    public function isFeatured()
    {
        return $this->featured;
    }

    /**
     * @param boolean $featured
     */
    public function setFeatured(bool $featured)
    {
        $this->featured = $featured;
    }

    /**
     * @return string
     */
    public function getFeaturedImage()
    {
        return 'uploads/posters/'.$this->featuredImage;
    }

    /**
     * @param string $featuredImage
     */
    public function setFeaturedImage(string $featuredImage)
    {
        $this->featuredImage = $featuredImage;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param ArrayCollection|Comment[] $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @param Comment $comment
     * @return bool
     */
    public function hasComment(Comment $comment)
    {
        return $this->comments->contains($comment);
    }

    /**
     * @param Comment $comment
     */
    public function addComment(Comment $comment)
    {
        if (!$this->hasComment($comment)) {
            $this->comments->add($comment);
        }
    }

    /**
     * @return int
     */
    public function getCommentCount()
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

    public function incrementCommentCount()
    {
        $this->commentCount++;
    }

    public function decrementCommentCount()
    {
        $this->commentCount--;
    }

    /**
     * @return ArrayCollection|Watchlist[]
     */
    public function getWatchlisted()
    {
        return $this->watchlisted;
    }

    /**
     * @param $watchlisted
     */
    public function setWatchlisted($watchlisted)
    {
        $this->watchlisted = $watchlisted;
    }

    /**
     * @param Watchlist $watchlist
     * @return bool
     */
    public function hasWatchlist(Watchlist $watchlist)
    {
        return $this->watchlisted->contains($watchlist);
    }

    /**
     * @param Watchlist $watchlist
     */
    public function addWatchlist(Watchlist $watchlist)
    {
        if (!$this->hasWatchlist($watchlist)) {
            $this->watchlisted->add($watchlist);
        }
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
    public function setWatchlistCount($watchlistCount)
    {
        $this->watchlistCount = $watchlistCount;
    }
}