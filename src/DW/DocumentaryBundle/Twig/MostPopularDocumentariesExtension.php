<?php

namespace DW\DocumentaryBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\DocumentaryBundle\Service\DocumentaryService;

class MostPopularDocumentariesExtension extends \Twig_Extension
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
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("mostPopularDocumentaries", [$this, "mostPopularDocumentaries"])
        ];
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function mostPopularDocumentaries(int $limit)
    {
        return $this->documentaryService->getMostPopularDocumentaries($limit);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'most_popular_documentaries_extension';
    }
}