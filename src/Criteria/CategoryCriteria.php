<?php

namespace App\Criteria;

use App\Entity\Documentary;
use App\Entity\User;

class CategoryCriteria
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $greaterThanEqual;

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
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getDocumentaryCountGreaterThanEqual(): ?int
    {
        return $this->greaterThanEqual;
    }

    /**
     * @param int $greaterThanEqual
     */
    public function setDocumentaryCountGreaterThanEqual(int $greaterThanEqual): void
    {
        $this->greaterThanEqual = $greaterThanEqual;
    }

    /**
     * @return array
     */
    public function getSort(): ?array
    {
        return $this->sort;
    }

    /**
     * @param array $sort
     */
    public function setSort(array $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}