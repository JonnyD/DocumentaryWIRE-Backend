<?php

namespace App\Object\Activity\Strategy\Concrete;

use App\Entity\Activity;
use App\Object\Activity\ActivityChild;
use App\Object\Activity\ActivityParent;
use App\Object\Activity\Data\CommentData;
use App\Object\Activity\Strategy\StrategyInterface;
use App\Service\CommentService;

class StrategyComment implements StrategyInterface
{
    /**
     * @var int
     */
    private $groupNumber;

    /**
     * @var int
     */
    private $previousGroupNumber;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @param int $groupNumber
     * @param int $previousGroupNumber
     * @param CommentService $commentService
     */
    public function __construct(
        int $groupNumber,
        int $previousGroupNumber,
        CommentService $commentService)
    {
        $this->groupNumber = $groupNumber;
        $this->previousGroupNumber = $previousGroupNumber;
        $this->commentService = $commentService;
    }

    /**
     * @param Activity $activityEntity
     * @return array
     */
    public function createActivity(Activity $activityEntity)
    {
        $commentId = $activityEntity->getObjectId();

        $user = $activityEntity->getUser();
        $name = $user->getName();
        $avatar = $user->getAvatar();
        $username = $user->getUsername();

        $comment = $this->commentService->getCommentById($commentId);
        $documentary = $comment->getDocumentary();

        $commentData = new CommentData();
        $commentData->setCommentId($comment->getId());
        $commentData->setCommentText($comment->getCommentText());
        $commentData->setDocumentaryId($documentary->getId());
        $commentData->setDocumentaryPoster($documentary->getPoster());
        $commentData->setDocumentarySlug($documentary->getSlug());

        $tempActivityArray = [];
        if ($this->groupNumber != $this->previousGroupNumber) {
            $parent = new ActivityParent();
            $parent->setData($commentData);
            $parent->setName($name);
            $parent->setAvatar($avatar);
            $parent->setUsername($username);

            $tempActivityArray['parent'] = $parent->toArray();
        } else {
            $child = new ActivityChild();
            $child->setData($commentData);
            $child->setName($name);
            $child->setAvatar($avatar);
            $child->setUsername($username);

            $tempActivityArray['child'][] = $child->toArray();
        }

        return $tempActivityArray;
    }
}