<?php

namespace App\Controller;

use App\Service\CategoryService;
use App\Service\DocumentaryService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class SitemapController extends AbstractFOSRestController implements ClassResourceInterface
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
     * @param DocumentaryService $documentaryService
     * @param CategoryService $categoryService
     */
    public function __construct(
        DocumentaryService $documentaryService,
        CategoryService $categoryService
    )
    {
        $this->documentaryService = $documentaryService;
        $this->categoryService = $categoryService;
    }

    /**
     * @FOSRest\Get("/sitemap", name="get_sitemap", options={ "method_prefix" = false })
     *
     * @return string
     */
    public function sitemapAction()
    {
        $baseLink = "http://localhost:8000";

        $lastUpdatedDocumentary = $this->documentaryService->getLastUpdatedDocumentary();
        $lastModifiedDocumentary = $lastUpdatedDocumentary->getUpdatedAt()->format('Y-m-d\TH:i:sP');

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://www.documentarywire.com/sitemap.xsl"?> <sitemapindex
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');

        $loc = $url = $baseLink . '/sitemap_page';
        $url = $rootNode->addChild('sitemap');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastModifiedDocumentary);

        $loc = $url = $baseLink . '/sitemap_category';
        $url = $rootNode->addChild('sitemap');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastModifiedDocumentary);

        $loc = $url = $baseLink . '/sitemap_documentary';
        $url = $rootNode->addChild('sitemap');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastModifiedDocumentary);

        $response = new Response($rootNode->asXML());
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    /**
     * @FOSRest\Get("/sitemap_page", name="get_sitemap_page", options={ "method_prefix" = false })
     *
     * @return string
     */
    public function sitemapPageAction()
    {
        $baseLink = "http://localhost:8000";

        $lastUpdatedDocumentary = $this->documentaryService->getLastUpdatedDocumentary();
        $lastModified = $lastUpdatedDocumentary->getUpdatedAt()->format('Y-m-d\TH:i:sP');

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://www.documentarywire.com/sitemap.xsl"?> <urlset
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        $loc = $baseLink;
        $url = $rootNode->addChild('url');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastModified);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '1.0');

        $loc = $baseLink . '/browse';
        $url = $rootNode->addChild('url');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastModified);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.8');

        $loc = $baseLink . '/contact';
        $url = $rootNode->addChild('url');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', "");
        $url->addChild('changefreq', 'yearly');
        $url->addChild('priority', '0.2');

        $response = new Response($rootNode->asXML());
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    /**
     * @FOSRest\Get("/sitemap_category", name="get_sitemap_category", options={ "method_prefix" = false })
     *
     * @return string
     */
    public function sitemapCategoryAction()
    {
        $baseLink = "http://localhost:8000";

        $categories = $this->categoryService->getEnabledCategoriesOrderedByName();

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://www.documentarywire.com/sitemap.xsl"?> <urlset
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($categories as $category) {
            $loc = $url = $baseLink . '/category/' . $category->getSlug();

            $lastModifiedDocumentary = $this->documentaryService->getLatestDocumentaryInCategory($category);
            $lastModified = $lastModifiedDocumentary->getUpdatedAt()->format('Y-m-d\TH:i:sP');

            $url = $rootNode->addChild('url');
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastModified);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.8');
        }

        $response = new Response($rootNode->asXML());
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    /**
     * @FOSRest\Get("/sitemap_documentary", name="get_sitemap_documentary", options={ "method_prefix" = false })
     *
     * @return string
     */
    public function sitemapDocumentaryAction()
    {
        $baseLink = "http://localhost:8000";

        $documentaries = $this->documentaryService->getPublishedDocumentaries();

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://www.documentarywire.com/sitemap.xsl"?> <urlset
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($documentaries as $documentary) {
            $loc = $url = $baseLink . '/' . $documentary->getSlug();

            $url = $rootNode->addChild('url');
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $documentary->getUpdatedAt()->format('Y-m-d\TH:i:sP'));
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        $response = new Response($rootNode->asXML());
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }
}