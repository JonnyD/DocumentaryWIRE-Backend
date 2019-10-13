<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Rooxie\OMDb;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Rooxie\Model\Movie;

class OMDBController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @FOSRest\Get("/omdb/search", name="get_documentaries_from_omdb", options={ "method_prefix" = false })
     *
     * @param Request $request
     */
    public function searchOMDBAction(Request $request)
    {
        $omdb = new OMDb($_ENV['OMDB_KEY']);

        $title = $request->query->get('title');
        if (!isset($title)) {
            //@TODO

            throw new AccessDeniedException();
        }

        $documentaries = $omdb->search($title);

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Methods: GET, POST',
            'Access-Control-Allow-Credentials: true',
            'Access-Control-Max-Age: 86400',
            'Access-Control-Request-Headers' => [' X-Requested-With'],
        ];

        return new JsonResponse($documentaries, 200, $headers);
    }
    /**
     *
     * @FOSRest\Get("/omdb/{imdbId}", name="get_documentaries_by_id_from_omdb", options={ "method_prefix" = false })
     *
     * @param Request $request
     */
    public function getByImdbIdAction(string $imdbId)
    {
        $omdb = new OMDb($_ENV['OMDB_KEY']);

        if (!isset($imdbId)) {
            //@TODO

            throw new AccessDeniedException();
        }

        /** @var Movie $documentary */
        $documentary = $omdb->getByImdbId($imdbId);
        $serialized = $this->serializeDocumentary($documentary);

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Methods: GET, POST',
            'Access-Control-Allow-Credentials: true',
            'Access-Control-Max-Age: 86400',
            'Access-Control-Request-Headers' => [' X-Requested-With'],
        ];

        return new JsonResponse($serialized, 200, $headers);
    }

    public function serializeDocumentary(Movie $documentary)
    {
        return [
            'imdbId' => $documentary->getImdbId(),
            'title' => $documentary->getTitle(),
            'year' => $documentary->getYear(),
            'released' => $documentary->getReleased(),
            'plot' => $documentary->getPlot(),
            'poster' => $documentary->getPosterUrl(),
            'imdbRating' => $documentary->getImdbRating(),
            'imdbVotes' => $documentary->getImdbVotes(),
            'duration' => $documentary->getRuntime()
        ];
    }
}