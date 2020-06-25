<?php

namespace App\Hydrator;

use App\Entity\Activity;
use App\Object\Activity\Strategy\DataStrategyContext;
use App\Service\CommentService;
use App\Service\DocumentaryService;
use Symfony\Component\HttpFoundation\Request;
use App\Object\Activity\Activity as ActivityObject;

class ActivityHydrator implements HydratorInterface
{
    /**
     * @var Activity
     */
    private $activityItem;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @param Activity $activityItem
     * @param Request $request
     * @param DocumentaryService $documentaryService
     * @param CommentService $commentService
     */
    public function __construct(
        Activity $activityItem,
        Request $request,
        DocumentaryService $documentaryService,
        CommentService $commentService)
    {
        $this->activityItem = $activityItem;
        $this->request = $request;
        $this->documentaryService = $documentaryService;
        $this->commentService = $commentService;
    }

    public function toArray()
    {
        $type = $this->activityItem->getType();
        $createdAt = $this->activityItem->getCreatedAt();

        $dataStrategyContext = new DataStrategyContext(
            $type,
            $this->request,
            $this->documentaryService,
            $this->commentService);
        $data = $dataStrategyContext->createData($this->activityItem);

        $user = $this->activityItem->getUser();
        $name = $user->getName();
        $avatar = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $user->getAvatar();
        $username = $user->getUsername();

        $activityObject = new ActivityObject();
        $activityObject->setId($this->activityItem->getId());
        $activityObject->setObjectId($this->activityItem->getObjectId());
        $activityObject->setName($name);
        $activityObject->setUsername($username);
        $activityObject->setAvatar($avatar);
        $activityObject->setData($data);
        $activityObject->setType($type);
        $activityObject->setCreatedAt($createdAt);
        $activityObject->setComponent($this->activityItem->getComponent());
        $activityObject->setGroupNumber($this->activityItem->getGroupNumber());

        return $activityObject->toArray();
    }
}