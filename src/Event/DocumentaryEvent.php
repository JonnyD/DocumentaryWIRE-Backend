<?php

namespace App\Event;

use App\Entity\Documentary;

class DocumentaryEvent
{
    /**
     * @var Documentary
     */
    private $documentary;

    /**
     * @param Documentary $documentary
     */
    public function __construct(Documentary $documentary)
    {
        $this->documentary = $documentary;
    }

    /**
     * @return Documentary
     */
    public function getDocumentary()
    {
        return $this->documentary;
    }
}