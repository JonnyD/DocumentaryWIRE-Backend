<?php

namespace App\Service;

use App\Entity\Season;
use App\Enum\Sync;
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

    /**
     * @param Season $season
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Season $season, string $sync = Sync::YES)
    {
        if ($season->getCreatedAt() == null) {
            $season->setCreatedAt(new \DateTime());
        } else {
            $season->setUpdatedAt(new \DateTime());
        }

        $this->seasonRepository->save($season, $sync);
    }
}