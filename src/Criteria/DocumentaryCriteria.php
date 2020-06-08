<?php

namespace App\Criteria;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\VideoSource;
use App\Enum\DocumentaryStatus;
use App\Enum\Featured;
use App\Enum\IsParent;
use Codeception\Lib\Generator\Feature;

class DocumentaryCriteria
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $featured;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $isParent;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var VideoSource
     */
    private $videoSource;

    /**
     * @var int
     */
    private $year;

    /**
     * @var string
     */
    private $duration;

    /**
     * @var User
     */
    private $addedBy;

    /**
     * @var array
     */
    private $sort;

    /**
     * @var int
     */
    private $limit;

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return boolean
     */
    public function isFeatured()
    {
        return ($this->featured === Featured::YES);
    }

    /**
     * @param string $featured
     * @throws \Exception
     */
    public function setFeatured(string $featured)
    {
        $hasFeatured = Featured::hasStatus($featured);
        if (!$hasFeatured) {
            throw new \Exception('Featured status does not exist');
        }

        $this->featured = $featured;
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
     * @throws \Exception
     */
    public function setStatus(string $status)
    {
        $hasStatus = DocumentaryStatus::hasStatus($status);
        if (!$hasStatus) {
            throw new \Exception('Status does not exist');
        }

        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getIsParent()
    {
        return $this->isParent;
    }

    /**
     * @param string $isParent
     */
    public function setIsParent(string $isParent)
    {
        $isParent = IsParent::hasStatus($isParent);
        if (!$isParent) {
            throw new \Exception('Is Parent status does not exist');
        }

        $this->isParent = $isParent;
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
     * @return VideoSource
     */
    public function getVideoSource()
    {
        return $this->videoSource;
    }

    /**
     * @param VideoSource $videoSource
     */
    public function setVideoSource(VideoSource $videoSource)
    {
        $this->videoSource = $videoSource;
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
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration(string $duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return User
     */
    public function getAddedBy()
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
     * @return array
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param array $sort
     */
    public function setSort(array $sort)
    {
        //@TODO check sort
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }
}