<?php

namespace DW\UserBundle\Criteria;

class UserCriteria
{
    /**
     * @var \DateTime
     */
    private $activatedAt;

    /**
     * @var bool
     */
    private $isActivated;

    /**
     * @var array
     */
    private $sort;

    /**
     * @var int
     */
    private $limit;

    /**
     * @return \DateTime
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * @param \DateTime $activatedAt
     */
    public function setActivatedAt(\DateTime $activatedAt)
    {
        $this->activatedAt = $activatedAt;
    }

    /**
     * @return boolean
     */
    public function isActivated()
    {
        return $this->isActivated;
    }

    /**
     * @param boolean $isActivated
     */
    public function setIsActivated(bool $isActivated)
    {
        $this->isActivated = $isActivated;
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