<?php

namespace DW\SiteBundle\Controller\Publc;

use DW\CategoryBundle\Service\CategoryService;
use DW\DocumentaryBundle\Service\DocumentaryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $documentaryService = $this->getDocumentaryService();
        $latestDocumentary = $documentaryService->getLatestDocumentary();
        $latestDocumentaryUpdatedAt = $latestDocumentary->getUpdatedAt()->format('Y-m-d\TH:i:sP');

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://www.documentarywire.com/sitemap.xsl"?> <sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');

        $loc = $url = $this->generateUrl('documentary_wire.sitemap_page', array(), true);
        $url = $rootNode->addChild('sitemap');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $latestDocumentaryUpdatedAt);

        $loc = $url = $this->generateUrl('documentary_wire.sitemap_category', array(), true);
        $url = $rootNode->addChild('sitemap');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $latestDocumentaryUpdatedAt);

        $loc = $url = $this->generateUrl('documentary_wire.sitemap_documentary', array(), true);
        $url = $rootNode->addChild('sitemap');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $latestDocumentaryUpdatedAt);

        $response = new Response($rootNode->asXML());
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    /**
     * @return Response
     */
    public function pageAction()
    {
        $documentaryService = $this->getDocumentaryService();
        $latestDocumentary = $documentaryService->getLatestDocumentary();
        $latestDocumentaryUpdatedAt = $latestDocumentary->getUpdatedAt()->format('Y-m-d\TH:i:sP');

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://www.documentarywire.com/sitemap.xsl"?> <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        $loc = $this->generateUrl('documentary_wire_homepage', array(), true);
        $url = $rootNode->addChild('url');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $latestDocumentaryUpdatedAt);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '1.0');

        $loc = $this->generateUrl('documentary_wire_browse', array(), true);
        $url = $rootNode->addChild('url');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $latestDocumentaryUpdatedAt);
        $url->addChild('changefreq', 'weekly');
        $url->addChild('priority', '0.8');

        $loc = $this->generateUrl('documentary_wire_all', array(), true);
        $url = $rootNode->addChild('url');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $latestDocumentaryUpdatedAt);
        $url->addChild('changefreq', 'monthly');
        $url->addChild('priority', '0.4');

        $loc = $this->generateUrl('documentary_wire.contact', array(), true);
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
     * @return Response
     */
    public function categoryAction()
    {
        $categoryService = $this->getCategoryService();
        $categories = $categoryService->getAllCategoriesWithDocumentaries();

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://www.documentarywire.com/sitemap.xsl"?> <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        $documentaryService = $this->getDocumentaryService();
        foreach ($categories as $category) {
            $loc = $url = $this->generateUrl('documentary_wire_show_category', array('slug' => $category->getSlug()), true);

            $latestDocumentary = $documentaryService->getLatestDocumentaryInCategory($category);
            $latestDocumentaryUpdatedAt = $latestDocumentary->getUpdatedAt()->format('Y-m-d\TH:i:sP');

            $url = $rootNode->addChild('url');
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $latestDocumentaryUpdatedAt);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.8');
        }

        $response = new Response($rootNode->asXML());
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    public function documentaryAction()
    {
        $documentaryService = $this->getDocumentaryService();
        $documentaries = $documentaryService->getPublishedDocumentaries();

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://www.documentarywire.com/sitemap.xsl"?> <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($documentaries as $documentary) {
            $loc = $url = $this->generateUrl('documentary_wire_show_documentary', array('slug' => $documentary->getSlug()), true);

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

    /**
     * @return CategoryService
     */
    private function getCategoryService()
    {
        return $this->get('dw.category_service');
    }

    /**
     * @return DocumentaryService
     */
    private function getDocumentaryService()
    {
        return $this->get('dw.documentary_service');
    }
}