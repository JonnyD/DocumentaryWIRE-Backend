<?php

namespace App\Controller;

use App\Criteria\ActivityCriteria;
use App\Entity\Activity;
use App\Enum\ActivityOrderBy;
use App\Enum\ActivityType;
use App\Enum\Order;
use App\Service\ActivityService;
use App\Service\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class YoutubeController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @FOSRest\Get("/youtube/search", name="get_youtube", options={ "method_prefix" = false })
     * @param Request $request
     * @return JsonResponse
     */
    public function searchYoutubeAction(Request $request)
    {
        $search = $request->query->get('q');

        $apiKey = $_ENV['YOUTUBE_KEY'];

        $link = "https://www.googleapis.com/youtube/v3/search?part=id%2C%20snippet&maxResults=50&order=relevance&q=". urlencode($search) . "&key=" . $apiKey;

        $video = file_get_contents($link);

        $video = json_decode($video, true);

        return new JsonResponse($video, 200, array('Access-Control-Allow-Origin'=> '*'));
    }

    /**
     * @FOSRest\Get("/youtube/{id}", name="get_youtube_by_id", options={ "method_prefix" = false })
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getByIdAction(Request $request, string $id)
    {
        $apiKey = $_ENV['YOUTUBE_KEY'];

        $link = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=".urlencode($id)."&key=" . $apiKey;

        $video = file_get_contents($link);

        $video = json_decode($video, true);

        if ($video['pageInfo']['totalResults'] === 0) {
            return new JsonResponse(null, 404, array('Access-Control-Allow-Origin'=> '*'));
        }

        return new JsonResponse($video, 200, array('Access-Control-Allow-Origin'=> '*'));
    }
}