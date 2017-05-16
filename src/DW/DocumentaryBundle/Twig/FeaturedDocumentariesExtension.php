<?php

namespace DW\DocumentaryBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\DocumentaryBundle\Service\DocumentaryService;

class FeaturedDocumentariesExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction("featuredDocumentaries", [$this, "featuredDocumentaries"])
        ];
    }

    /**
     * @return ArrayCollection|Documentary[]
     */
    public function featuredDocumentaries()
    {
        return $this->documentaryService->getFeaturedDocumentaries();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'featured_documentaries_extension';
    }
}
