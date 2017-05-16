<?php

namespace DW\ActivityBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\ActivityBundle\Entity\Activity;
use DW\ActivityBundle\Service\ActivityService;

class RecentActivityAllExtension extends \Twig_Extension
{
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @param ActivityService $activityService
     */
    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("recentActivityAll", [$this, "recentActivityAll"])
        ];
    }

    /**
     * @return ArrayCollection|Activity[]
     */
    public function recentActivityAll()
    {
        return $this->activityService->getRecentActivityForWidget();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recent_activity_all_extension';
    }
}
