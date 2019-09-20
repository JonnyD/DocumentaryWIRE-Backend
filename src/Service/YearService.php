<?php

namespace App\Service;

use App\Criteria\DocumentaryCriteria;
use App\Entity\Category;
use App\Entity\Documentary;
use App\Enum\DocumentaryOrderBy;
use App\Enum\DocumentaryStatus;
use App\Enum\Order;
use App\Repository\DocumentaryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

class YearService
{
    /**
     * @var DocumentaryRepository
     */
    private $documentaryRepository;

    /**
     * @param DocumentaryRepository $documentaryRepository
     */
    public function __construct(DocumentaryRepository $documentaryRepository)
    {
        $this->documentaryRepository = $documentaryRepository;
    }

    public function getYears()
    {
        return $this->documentaryRepository->findYears();
    }
}