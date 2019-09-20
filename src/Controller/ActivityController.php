<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Service\ActivityService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;

class ActivityController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var ActivityService
     */
    private $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * @FOSRest\Get("/activity", name="get_activity", options={ "method_prefix" = false })
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $activities = $this->activityService->getRecentActivityForWidget();

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        return new JsonResponse($activities, 200, $headers);
    }
}