<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Service\ActivityService;
use App\Service\YearService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;

class YearController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var YearService
     */
    private $yearService;

    public function __construct(YearService $yearService)
    {
        $this->yearService = $yearService;
    }

    /**
     * @FOSRest\Get("/year", name="get_years", options={ "method_prefix" = false })
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $years = $this->yearService->getYears();

        return $this->createApiResponse($years, 200);
    }

    public function getYearAction(int $id)
    {
        $year = $this->yearService->getYearById($id);
        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];
    }
}