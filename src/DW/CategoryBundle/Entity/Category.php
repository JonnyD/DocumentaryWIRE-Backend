<?php

namespace DW\CategoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use DW\BaseBundle\Traits\Sluggable;
use DW\DocumentaryBundle\Entity\Documentary;
use Gedmo\Timestampable\Traits\Timestampable;

class Category
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
    private $name;

    /**
     * @var int
     */
    private $count;

    /**
     * @var ArrayCollection|Documentary[]
     */
    private $documentaries;

    public function __construct()
    {
        $this->documentaries = new ArrayCollection();
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection|Documentary[]
     */
    public function getDocumentaries()
    {
        return $this->documentaries;
    }

    /**
     * @param ArrayCollection|Documentary[] $documentaries
     */
    public function setDocumentaries($documentaries)
    {
        $this->documentaries = $documentaries;
    }

    /**
     * @param Documentary $documentary
     * @return bool
     */
    public function hasDocumentary(Documentary $documentary)
    {
        return $this->documentaries->contains($documentary);
    }

    /**
     * @param Documentary $documentary
     */
    public function addDocumentary(Documentary $documentary)
    {
        if (!$this->hasDocumentary($documentary)) {
            $this->documentaries->add($documentary);
        }
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count)
    {
        $this->count = $count;
    }

    public function incrementCount()
    {
        $this->count++;
    }

    public function decrementCount()
    {
        $this->count--;
    }
}