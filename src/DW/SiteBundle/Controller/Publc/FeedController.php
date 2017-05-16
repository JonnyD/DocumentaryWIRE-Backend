<?php

namespace DW\SiteBundle\Controller\Publc;

use DW\DocumentaryBundle\Service\DocumentaryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FeedController extends Controller
{
    /**
     * @return Response
     */
    public function feedAction()
    {
        $documentaryService = $this->getDocumentaryService();
        $documentaries = $documentaryService->getLatestDocumentaries(10);

        $feed = $this->getFeedManager()->get('documentary');
        $feed->addFromArray($documentaries);

        $response = new Response($feed->render('rss')); // or 'atom'
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    private function getFeedManager()
    {
        return $this->get('eko_feed.feed.manager');
    }

    /**
     * @return DocumentaryService
     */
    private function getDocumentaryService()
    {
        return $this->get('dw.documentary_service');
    }
}