<?php

namespace DW\ActivityBundle\Controller\Publc;

use DW\ActivityBundle\Criteria\ActivityCriteria;
use DW\ActivityBundle\Enum\OrderBy;
use DW\ActivityBundle\Service\ActivityService;
use DW\BaseBundle\Enum\Order;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends Controller
{
    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->get('dw.activity_service');
    }
}