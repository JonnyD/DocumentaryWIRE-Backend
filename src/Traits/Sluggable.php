<?php

namespace App\Traits;

trait Sluggable
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $slug;

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }
}