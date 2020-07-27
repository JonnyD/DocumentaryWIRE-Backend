<?php

namespace App\Service;

use App\Entity\Season;
use App\Enum\Sync;
use App\Enum\UpdateTimestamps;
use App\Repository\SeasonRepository;

class SeasonService
{
    /**
     * @var SeasonRepository
     */
    private $seasonRepository;

    /**
     * @param SeasonRepository $seasonRepository
     */
    public function __construct(
        SeasonRepository $seasonRepository
    )
    {
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param Season $season
     * @param string $updateTimestamps
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Season $season, string $updateTimestamps = UpdateTimestamps::YES, string $sync = Sync::YES)
    {
        if ($updateTimestamps === UpdateTimestamps::YES) {
            $currentDateTime = new \DateTime();

            if ($season->getCreatedAt() == null) {
                $season->setCreatedAt($currentDateTime);
            } else {
                $season->setUpdatedAt($currentDateTime);
            }
        }

        $this->seasonRepository->save($season, $sync);
    }
}