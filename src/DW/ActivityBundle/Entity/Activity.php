<?php

namespace DW\ActivityBundle\Entity;

use DW\UserBundle\Entity\User;
use Gedmo\Timestampable\Traits\Timestampable;

class Activity
{
    use Timestampable;

    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $user;

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
    private $objectId;

    /**
     * @var string
     */
    private $data;

    /**
     * @var int
     */
    private $groupNumber;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

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
    public function getData()
    {
        return unserialize($this->data);
    }

    /**
     * @param string $data
     */
    public function setData(string $data)
    {
        $this->data = $data;
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
}