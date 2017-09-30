<?php

namespace DW\WatchlistBundle\Controller\Publc;

use DW\ActivityBundle\Criteria\ActivityCriteria;
use DW\ActivityBundle\Enum\ActivityType;
use DW\ActivityBundle\Enum\ComponentType;
use DW\ActivityBundle\Service\ActivityService;
use DW\DocumentaryBundle\Service\DocumentaryService;
use DW\UserBundle\Service\SecurityService;
use DW\WatchlistBundle\Enum\ActionType;
use DW\WatchlistBundle\Service\WatchlistService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WatchlistController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function likeAction(Request $request)
    {
        $actionType = $request->get('actionType');
        $documentaryId = $request->get('documentaryId');

        $securityService = $this->getSecurityService();
        $user = $securityService->getLoggedInUser();

        if ($user != null) {
            $documentaryService = $this->getDocumentaryService();
            $documentary = $documentaryService->getDocumentaryById($documentaryId);

            if ($documentary) {
                $watchlistService = $this->getWatchlistService();
                $hasWatchlisted = $watchlistService->hasWatchlisted($user, $documentary);

                if ($actionType === ActionType::LIKE) {
                    if (!$hasWatchlisted) {
                        $watchlistService->watchlistDocumentary($user, $documentary);
                    }
                } else if ($actionType === ActionType::UNLIKE) {
                    if ($hasWatchlisted) {
                        $watchlist = $watchlistService->getWatchlistByUserAndDocumentary($user, $documentary);
                        $watchlistService->unwatchlistDocumentary($watchlist);
                    }
                }
            }
        }
        $headers = array(
            'Content-Type' => 'application/json'
        );
        $response = array("code" => 100, "success" => true, "error" => "");
        return new Response(json_encode($response), 200, $headers);
    }

    /**
     * @return SecurityService
     */
    private function getSecurityService()
    {
        return $this->get('dw.security_service');
    }

    /**
     * @return DocumentaryService
     */
    private function getDocumentaryService()
    {
        return $this->get('dw.documentary_service');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->get('dw.activity_service');
    }

    /**
     * @return WatchlistService
     */
    private function getWatchlistService()
    {
        return $this->get('dw.watchlist_service');
    }
}