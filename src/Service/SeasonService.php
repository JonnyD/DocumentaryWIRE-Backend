<?php

namespace App\Service;

use App\Entity\Season;
use App\Repository\SeasonRepository;

class SeasonService
{
    /**
     * @var SeasonRepository
     */
    private $seasonRepository;

    public function __construct(
        SeasonRepository $seasonRepository
    )
    {
        $this->seasonRepository = $seasonRepository;
    }

    public function save(Season $season, $sync = true)
    {
        $this->seasonRepository->save($season, $sync);
    }
}