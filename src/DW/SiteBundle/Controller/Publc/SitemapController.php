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

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://documentarywire.com/sitemap.xsl"?> <sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');

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
    public function sitemapXSLAction()
    {
        $response = new Response('<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>XML Sitemap</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<meta name="robots" content="noindex,follow" />
				<style type="text/css">
					body { font-family:"Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana; font-size:11px; }					
					h1 { margin: 5px; }
					#intro { margin: 20px 0 20px 5px; color: gray; }					
					#intro p { display: block; line-height: 6px; }					
					td { font-size:11px; }				
					th { text-align:left; padding-right:30px; }					
					tr.high { background-color:whitesmoke; }					
					#footer { margin: 10px 0 0 5px; color:gray; }										
					a { color:black; }
				</style>
			</head>
			<body>
				<xsl:apply-templates></xsl:apply-templates>
				<div id="footer">
					This XSLT template is released under the GPL and free to use.
				</div>
			</body>
		</html>
	</xsl:template>
	
	
	<xsl:template match="sitemap:urlset">
        <h1>XML Sitemap</h1>
 		<div id="intro">
			<p>This is a XML Sitemap which is supposed to be processed by search engines which follow the XML Sitemap standard.</p>
			<p>You can find more information about XML sitemaps on <a rel="nofollow" href="https://www.sitemaps.org/">sitemaps.org</a> and 
			Google\'s <a rel="nofollow" href="https://code.google.com/p/sitemap-generators/wiki/SitemapGenerators">list of sitemap programs</a>.</p>
			<div><a href="https://topdocumentaryfilms.com/sitemap.xml">&#8593; Sitemap Index</a></div>
		</div>
		<div id="content">
			<table cellpadding="5">
				<tr style="border-bottom:1px black solid;">
					<th>URL</th>
					<th>Priority</th>
					<th>Change frequency</th>
					<th>Last modified (GMT)</th>
				</tr>
				<xsl:variable name="lower" select="\'abcdefghijklmnopqrstuvwxyz\'"/>
				<xsl:variable name="upper" select="\'ABCDEFGHIJKLMNOPQRSTUVWXYZ\'"/>
				<xsl:for-each select="./sitemap:url">
					<tr>
						<xsl:if test="position() mod 2 != 1">
							<xsl:attribute  name="class">high</xsl:attribute>
						</xsl:if>
						<td>
							<xsl:variable name="itemURL">
								<xsl:value-of select="sitemap:loc"/>
							</xsl:variable>
							<a href="{$itemURL}">
								<xsl:value-of select="sitemap:loc"/>
							</a>
						</td>
						<td>
							<xsl:value-of select="concat(sitemap:priority*100,\'%\')"/>
						</td>
						<td>
							<xsl:value-of select="concat(translate(substring(sitemap:changefreq, 1, 1),concat($lower, $upper),concat($upper, $lower)),substring(sitemap:changefreq, 2))"/>
						</td>
						<td>
							<xsl:value-of select="concat(substring(sitemap:lastmod,0,11),concat(\' \', substring(sitemap:lastmod,12,5)))"/>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</div>
	</xsl:template>
	
	
	<xsl:template match="sitemap:sitemapindex">
        <h1>XML Sitemap Index</h1>
 		<div id="intro">
			<p>This is a XML Sitemap which is supposed to be processed by search engines which follow the XML Sitemap standard.</p>
			<p>You can find more information about XML sitemaps on <a rel="nofollow" href="https://www.sitemaps.org/">sitemaps.org</a> and 
			Google\'s <a rel="nofollow" href="https://code.google.com/p/sitemap-generators/wiki/SitemapGenerators">list of sitemap programs</a>.</p>
			<p>This file contains links to sub-sitemaps, follow them to see the actual sitemap content.</p>
		</div>
		<div id="content">
			<table cellpadding="5">
				<tr style="border-bottom:1px black solid;">
					<th>URL of sub-sitemap</th>
					<th>Last modified (GMT)</th>
				</tr>
				<xsl:for-each select="./sitemap:sitemap">
					<tr>
						<xsl:if test="position() mod 2 != 1">
							<xsl:attribute  name="class">high</xsl:attribute>
						</xsl:if>
						<td>
							<xsl:variable name="itemURL">
								<xsl:value-of select="sitemap:loc"/>
							</xsl:variable>
							<a href="{$itemURL}">
								<xsl:value-of select="sitemap:loc"/>
							</a>
						</td>
						<td>
							<xsl:value-of select="concat(substring(sitemap:lastmod,0,11),concat(\' \', substring(sitemap:lastmod,12,5)))"/>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</div>
	</xsl:template>
</xsl:stylesheet>');
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

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://documentarywire.com/sitemap.xsl"?> <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        $loc = $this->generateUrl('dw.home', array(), true);
        $url = $rootNode->addChild('url');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $latestDocumentaryUpdatedAt);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '1.0');

        $loc = $this->generateUrl('dw.browse_documentaries', array(), true);
        $url = $rootNode->addChild('url');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $latestDocumentaryUpdatedAt);
        $url->addChild('changefreq', 'weekly');
        $url->addChild('priority', '0.8');

        $loc = $this->generateUrl('dw.list_documentaries', array(), true);
        $url = $rootNode->addChild('url');
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $latestDocumentaryUpdatedAt);
        $url->addChild('changefreq', 'monthly');
        $url->addChild('priority', '0.4');

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

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://documentarywire.com/sitemap.xsl"?> <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        $documentaryService = $this->getDocumentaryService();
        foreach ($categories as $category) {
            $loc = $url = $this->generateUrl('dw.show_category', array('slug' => $category->getSlug()), true);

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

        $rootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="http://documentarywire.com/sitemap.xsl"?> <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($documentaries as $documentary) {
            $loc = $url = $this->generateUrl('dw.show_documentary', array('slug' => $documentary->getSlug()), true);

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