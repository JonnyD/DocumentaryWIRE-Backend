<?php

namespace App\Controller;

use App\Service\CategoryService;
use App\Service\DocumentaryService;
use App\Service\XmlService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class RssFeedController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * @var XmlService
     */
    private $xmlService;

    /**
     * @param DocumentaryService $documentaryService
     * @param CategoryService $categoryService
     * @param XmlService $xmlService
     */
    public function __construct(
        DocumentaryService $documentaryService,
        CategoryService $categoryService,
        XmlService $xmlService
    )
    {
        $this->documentaryService = $documentaryService;
        $this->categoryService = $categoryService;
        $this->xmlService = $xmlService;
    }

    /**
     * @FOSRest\Get("/rss/site", name="get_site_rss", options={ "method_prefix" = false })
     *
     * @return string
     */
    public function siteFeedAction()
    {
        $limitAmountOfDocumentaries = 20;
        $documentaries = $this->documentaryService->getLatestDocumentaries($limitAmountOfDocumentaries);

        $xml = $this->xmlService->generateXml($documentaries);

        $response = new Response();
        $response->headers->set("Content-type", "text/xml");
        $response->setContent($xml);
        return $response;
    }

    /**
     * @FOSRest\Get("/rss/category/{slug}", name="get_site_rss", options={ "method_prefix" = false })
     *
     * @param string $slug
     * @return string
     */
    public function categoryFeedAction(string $slug)
    {
        $category = $this->categoryService->getCategoryBySlug($slug);
        $documentaries = $this->documentaryService->getDocumentariesByCategory($category);

        $xml = $this->xmlService->generateXml($documentaries);

        $response = new Response();
        $response->headers->set("Content-type", "text/xml");
        $response->setContent($xml);
        return $response;
    }
}