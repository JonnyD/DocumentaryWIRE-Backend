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
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @param DocumentaryService $documentaryService
     */
    public function __construct(DocumentaryService $documentaryService)
    {
        $this->documentaryService = $documentaryService;
    }

    /**
     * @return array
     */
    public function getYearsExtractedFromDocumentaries()
    {
        return $this->documentaryService->getYearsExtractedFromDocumentaries();
    }
}