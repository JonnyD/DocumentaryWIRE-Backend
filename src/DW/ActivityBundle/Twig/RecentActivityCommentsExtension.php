<?php

namespace DW\ActivityBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DW\ActivityBundle\Entity\Activity;
use DW\ActivityBundle\Service\ActivityService;

class RecentActivityCommentsExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction("recentActivityComments", [$this, "recentActivityComments"])
        ];
    }

    /**
     * @return ArrayCollection|Activity[]
     */
    public function recentActivityComments()
    {
        return $this->activityService->getRecentActivityCommentsForWidget();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recent_activity_comments_extension';
    }
}
