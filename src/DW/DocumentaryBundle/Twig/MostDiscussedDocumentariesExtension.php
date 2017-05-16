<?php

namespace DW\DocumentaryBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\DocumentaryBundle\Service\DocumentaryService;

class MostDiscussedDocumentariesExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction("mostDiscussedDocumentaries", [$this, "mostDiscussedDocumentaries"])
        ];
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function mostDiscussedDocumentaries(int $limit)
    {
        return $this->documentaryService->getMostDiscussedDocumentaries($limit);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'most_discussed_documentaries_extension';
    }
}