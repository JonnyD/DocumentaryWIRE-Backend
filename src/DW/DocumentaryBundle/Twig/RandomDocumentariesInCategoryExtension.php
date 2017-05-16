<?php

namespace DW\DocumentaryBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\CategoryBundle\Entity\Category;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\DocumentaryBundle\Service\DocumentaryService;

class RandomDocumentariesInCategoryExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction("randomDocumentariesInCategory", [$this, "randomDocumentariesInCategory"])
        ];
    }

    /**
     * @param Category $category
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function randomDocumentariesInCategory(Category $category, int $limit)
    {
        return $this->documentaryService->getRandomDocumentariesInCategoryByCriteria($category, $limit);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'random_documentaries_in_category_extension';
    }
}
