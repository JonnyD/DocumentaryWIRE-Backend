<?php

namespace DW\ActivityBundle\Criteria;

use DW\UserBundle\Entity\User;

class ActivityCriteria
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var int
     */
    private $objectId;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $component;

    /**
     * @var int
     */
    private $groupNumber;

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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     */
    public function setObjectId(int $objectId)
    {
        $this->objectId = $objectId;
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
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @param string $component
     */
    public function setComponent(string $component)
    {
        $this->component = $component;
    }

    /**
     * @return int
     */
    public function getGroupNumber()
    {
        return $this->groupNumber;
    }

    /**
     * @param int $groupNumber
     */
    public function setGroupNumber(int $groupNumber)
    {
        $this->groupNumber = $groupNumber;
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