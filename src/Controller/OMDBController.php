<?php

namespace App\Controller;

use App\Enum\DocumentaryType;
use App\Object\DTO\Episode;
use App\Object\DTO\Season;
use App\Object\DTO\Series;
use App\Object\OMDb;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use PhpParser\Comment\Doc;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class OMDBController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @FOSRest\Get("/omdb/search", name="get_documentaries_from_omdb", options={ "method_prefix" = false })
     *
     * @param Request $request
     */
    public function searchOMDBAction(Request $request)
    {
        $type = $request->query->get('type');
        $hasType = DocumentaryType::hasType($type);
        if (!$hasType) {
            //@TODO
            throw new \Exception();
        }

        $omdb = new OMDb();
        $omdb->setParams([
            'type' => $type,
            'plot' => 'full',
            'apikey' => $_ENV['OMDB_KEY']
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Methods: GET, POST',
            'Access-Control-Allow-Credentials: true',
            'Access-Control-Max-Age: 86400',
            'Access-Control-Request-Headers' => [' X-Requested-With'],
        ];

        $title = $request->query->get('title');
        if (!isset($title)) {
            return new JsonResponse(null, 400, $headers);
        }

        $searchedDocumentaries = $omdb->search($title);
        var_dump($searchedDocumentaries['Search']); die();

        $serialized = [];
        foreach ($searchedDocumentaries as $documentary) {
            switch($type) {
                case DocumentaryType::MOVIE:
                    $serialized[] = $this->serializeMovie($documentary);
                break;
                case DocumentaryType::SERIES:
                    $serialized[] = $this->serializeSeries($documentary);
                break;
            }
        }

        return new JsonResponse($serialized, 200, $headers);
    }
    /**
     *
     * @FOSRest\Get("/omdb/{imdbId}", name="get_documentaries_by_id_from_omdb", options={ "method_prefix" = false })
     *
     * @param Request $request
     */
    public function getByImdbIdAction(string $imdbId, Request $request)
    {
        $omdb = new \App\Object\OMDb();
        $omdb->setParams([
            'plot' => 'full',
            'apikey' => $_ENV['OMDB_KEY']
        ]);

        if (!isset($imdbId)) {
            //@TODO

            throw new AccessDeniedException();
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Methods: GET, POST',
            'Access-Control-Allow-Credentials: true',
            'Access-Control-Max-Age: 86400',
            'Access-Control-Request-Headers' => [' X-Requested-With'],
        ];

        $result = $omdb->get_by_id($imdbId);
        if ($result['Response'] == false) {
            $error = $result['Error'];
            return new JsonResponse($error, 404, $headers);
        }

        $typeFromAPI = $result['Type'];
        if ($typeFromAPI === 'movie') {
            $type = DocumentaryType::MOVIE;
        } else if ($typeFromAPI === 'series') {
            $type = DocumentaryType::SERIES;
        } else if ($typeFromAPI == DocumentaryType::EPISODE) {
            $type = DocumentaryType::EPISODE;
        } else {
            return new JsonResponse(null, 400, $headers);
        }

        if ($type === DocumentaryType::SERIES) {
            $seriesDTO = $this->createSeriesDTO($result, $omdb, $imdbId);
            $result = $this->serializeSeries($seriesDTO);
        } else if ($type === DocumentaryType::MOVIE) {
            $result = $this->serializeMovie($result);
        } else if ($type === DocumentaryType::EPISODE) {
            $result = $this->serializeEpisode($result);
        } else {
            return new JsonResponse(null, 400, $headers);
        }

        return new JsonResponse($result, 200, $headers);
    }

    private function createSeriesDTO(array $result, $omdb, $imdbId)
    {
        $seriesDTO = new Series();
        $seriesDTO->setTitle($result['Title']);
        $seriesDTO->setPlot($result['Plot']);
        $seriesDTO->setPoster($result['Poster']);
        $seriesDTO->setImdbId($result['imdbID']);
        $seriesDTO->setImdbRating($result['imdbRating']);
        $seriesDTO->setImdbVotes($result['imdbVotes']);

        $totalSeasons = $result['totalSeasons'];

        for ($i = 1; $i < $totalSeasons; $i++) {
            $season = $omdb->get_by_id($imdbId, $i);
            $seasonDTO = new Season();
            $seasonDTO->setNumber($i);

            $seriesDTO->addSeason($seasonDTO);

            $episodeSummaries = $season['Episodes'];
            for ($j = 0; $j < count($episodeSummaries); $j++) {
                $episodeSummary = $episodeSummaries[$j];
                $episodeIMDbID = $episodeSummary['imdbID'];

                $episode = $omdb->get_by_id($episodeIMDbID);
                if ($episode['Response'] === false) {
                    continue;
                }

                $episodeDTO = new Episode();
                $episodeDTO->setNumber($episode['Episode']);
                $episodeDTO->setTitle($episode['Title']);
                $episodeDTO->setYear($episode['Year']);
                $episodeDTO->setDuration($episode['Runtime']);
                $episodeDTO->setPlot($episode['Plot']);
                $episodeDTO->setThumbnail($episode['Poster']);
                $episodeDTO->setIMDbID($episode['imdbID']);
                $episodeDTO->setImdbRating($episode['imdbRating']);
                $episodeDTO->setImdbVotes($episode['imdbVotes']);

                $seasonDTO->addEpisode($episodeDTO);
            }
        }

        return $seriesDTO;
    }

    /**
     * @param array $result
     * @return array
     */
    public function serializeMovie(array $result)
    {
        $serialized = [
            'title' => $result['Title'],
            'year' => $result['Year'],
            'imdbId' => $result['imdbID'],
            'type' => $result['Type'],
            'poster' => $result['Poster']
        ];

        if (isset($result['imdbVotes'])) {
            $serialized['imdbVotes'] = $result['imdbVotes'];
        }

        if (isset($result['imdbRating'])) {
            $serialized['imdbRating'] = $result['imdbRating'];
        }

        if (isset($result['Plot'])) {
            $serialized['storyline'] = $result['Plot'];
        }

        if (isset($result['Runtime'])) {
            $serialized['duration'] = $result['Runtime'];
        }

        return $serialized;
    }

    /**
     * @param Series $series
     * @return array
     */
    public function serializeSeries(Series $series)
    {
        $seriesArray = [
            'imdbId' => $series->getImdbId(),
            'title' => $series->getTitle(),
            'storyline' => $series->getPlot(),
            'poster' => $series->getPoster(),
            'imdbRating' => $series->getImdbRating(),
            'imdbVotes' => $series->getImdbVotes()
        ];

        $seasonsArray = [];
        foreach ($series->getSeasons() as $season) {
            $seasonArray = [
                'number' => $season->getNumber()
            ];

            $episodesArray = [];
            foreach ($season->getEpisodes() as $episode) {
                $episodeArray = [
                    'number' => $episode->getNumber(),
                    'title' => $episode->getTitle(),
                    'year' => $episode->getYear(),
                    'length' => $episode->getDuration(),
                    'storyline' => $episode->getPlot(),
                    'thumbnail' => $episode->getThumbnail(),
                    'imdbId' => $episode->getImdbID(),
                    'imdbRating' => $episode->getImdbRating(),
                    'imdbVotes' => $episode->getImdbVotes()
                ];

                $episodesArray[] = $episodeArray;
            }

            $seasonArray['episodes'] = $episodesArray;

            $seasonsArray[] = $seasonArray;
        }

        $seriesArray['seasons'] = $seasonsArray;

        return $seriesArray;
    }

    /**
     * @param array $result
     * @return array
     */
    public function serializeEpisode(array $result)
    {
        $serialized = [
            'title' => $result['Title'],
            'year' => $result['Year'],
            'seasonNumber' => $result['Season'],
            'episodeNumber' => $result['Episode'],
            'length' => $result['Runtime'],
            'storyline' => $result['Plot'],
            'poster' => $result['Poster'],
            'imdbId' => $result['imdbID']
        ];

        return $serialized;
    }
}