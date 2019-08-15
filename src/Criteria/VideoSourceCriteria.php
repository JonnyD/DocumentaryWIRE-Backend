<?php

namespace App\Criteria;

use App\Entity\VideoSource;

class VideoSourceCriteria
{
    /**
     * @var bool
     */
    private $embedAllowed;

    /**
     * @var string
     */
    private $status;

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
}