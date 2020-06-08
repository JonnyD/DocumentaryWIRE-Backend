<?php

namespace App\Criteria;

use App\Entity\VideoSource;
use App\Enum\EmbedAllowed;
use App\Enum\VideoSourceStatus;

class VideoSourceCriteria
{
    /**
     * @var string
     */
    private $embedAllowed;

    /**
     * @var string
     */
    private $status;

    /**
     * @return bool
     */
    public function isEmbedAllowed(): ?string
    {
        return $this->embedAllowed;
    }

    /**
     * @param string $embedAllowed
     * @throws \Exception
     */
    public function setEmbedAllowed(string $embedAllowed): void
    {
        $hasStatus = EmbedAllowed::hasStatus($embedAllowed);
        if (!$hasStatus) {
            throw new \Exception('Wrong embed allowed');
        }

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
     * @throws \Exception
     */
    public function setStatus(string $status): void
    {
        $hasStatus = VideoSourceStatus::hasStatus($status);
        if (!$hasStatus) {
            throw new \Exception('Video source status does not exist');
        }

        $this->status = $status;
    }
}