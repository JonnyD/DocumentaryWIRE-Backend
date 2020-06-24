<?php

namespace App\Object\Activity\Strategy\Concrete;

use App\Entity\Activity;
use App\Object\Activity\ActivityChild;
use App\Object\Activity\ActivityParent;
use App\Object\Activity\Data\CommentData;
use App\Object\Activity\Data\Data;
use App\Object\Activity\Strategy\StrategyInterface;
use App\Service\CommentService;
use Symfony\Component\HttpFoundation\Request;

class StrategyComment implements StrategyInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * StrategyComment constructor.
     * @param Request $request
     * @param CommentService $commentService
     */
    public function __construct(
        Request $request,
        CommentService $commentService)
    {
        $this->request = $request;
        $this->commentService = $commentService;
    }

    /**
     * @param Activity $activityEntity
     * @return Data
     */
    public function createData(Activity $activityEntity)
    {
        $commentId = $activityEntity->getObjectId();
        $comment = $this->commentService->getCommentById($commentId);
        $documentary = $comment->getDocumentary();


        $commentData = new CommentData();
        $commentData->setCommentId($comment->getId());
        $commentData->setCommentText($comment->getCommentText());
        $commentData->setDocumentaryId($documentary->getId());
        $poster = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPoster();
        $commentData->setDocumentaryPoster($poster);
        $commentData->setDocumentarySlug($documentary->getSlug());
        $commentData->setDocumentaryTitle($documentary->getTitle());

        return $commentData;
    }
}