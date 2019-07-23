<?php

namespace App\Criteria;

class VideoSourceCriteria
{
    /**
     * @var bool
     */
    private $embedAllowed;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @return bool
     */
    public function isEmbedAllowed(): ?bool
    {
        return $this->embedAllowed;
    }

    /**
     * @param bool $embedAllowed
     */
    public function setEmbedAllowed(bool $embedAllowed): void
    {
        $this->embedAllowed = $embedAllowed;
    }

    /**
     * @return bool
     */
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}