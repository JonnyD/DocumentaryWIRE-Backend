<?php
 namespace App\Object\Activity;

 use App\Object\Activity\Data\Data;

class Activity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $avatar;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var int
     */
    private $groupNumber;

    /**
     * @var string
     */
    private $component;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @param Data $data
     */
    public function setData(Data $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
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
      * @return array
      */
     public function toArray()
     {
       return [
           'id' => $this->id,
           'type' => $this->type,
             'data' => $this->data->toArray(),
           'component' => $this->component,
           'groupNumber' => $this->groupNumber,
            'user' => [
                'name' => $this->name,
                'username' => $this->username,
                'avatar' => $this->avatar
            ],
            'createdAt' => $this->createdAt
         ];
     }
 }