<?php

namespace DW\DocumentaryBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\DocumentaryBundle\Service\DocumentaryService;

class LatestDocumentariesExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction("latestDocumentaries", [$this, "latestDocumentaries"])
        ];
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function latestDocumentaries(int $limit)
    {
        return $this->documentaryService->getLatestDocumentaries($limit);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'latest_documentaries_extension';
    }
}
