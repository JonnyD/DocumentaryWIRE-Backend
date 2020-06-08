<?php

namespace App\Criteria;

use App\Entity\Documentary;
use App\Entity\User;

class WatchlistCriteria
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Documentary
     */
    private $documentary;

    /**
     * @var array
     */
    private $sort;

    /**
     * @var int
     */
    private $limit;

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Documentary
     */
    public function getDocumentary(): ?Documentary
    {
        return $this->documentary;
    }

    /**
     * @param Documentary $documentary
     */
    public function setDocumentary(Documentary $documentary): void
    {
        $this->documentary = $documentary;
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
        //@TODO check sort
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