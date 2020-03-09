<?php

namespace App\Object\Activity;

use App\Object\Activity\Activity as ActivityObject;

class ActivityItemObject
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var ActivityObject
     */
    private $parent;

    /**
     * @var ActivityObject[]
     */
    private $children;

    public function __construct()
    {
        $this->children = [];
    }

    /**
     * @return string
     */
    public function getType(): string
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
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return Activity
     */
    public function getParent(): Activity
    {
        return $this->parent;
    }

    /**
     * @param Activity $parent
     */
    public function setParent(Activity $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return ActivityObject[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param ActivityObject[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @param ActivityObject $activityObject
     */
    public function addChild(ActivityObject $activityObject)
    {
        $this->children[] = $activityObject;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $children = [];
        foreach ($this->children as $child) {
            $children[] = $child->toArray();
        }

        $array = [
            'type' => $this->type,
            'created' => $this->created,
            'parent' => $this->parent->toArray()
        ];

        if (count($children) > 0) {
            $array['children'] = $children;
        }

        return $array;
    }
}