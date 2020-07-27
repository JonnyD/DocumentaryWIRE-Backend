<?php

namespace App\Service;

use App\Criteria\CommentCriteria;
use App\Entity\Comment;
use App\Entity\Documentary;
use App\Entity\User;
use App\Enum\CommentStatus;
use App\Enum\Sync;
use App\Enum\UpdateTimestamps;
use App\Event\CommentEvent;
use App\Event\CommentEvents;
use App\Repository\CommentRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentService
{
    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param CommentRepository $commentRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        CommentRepository $commentRepository,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->commentRepository = $commentRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param User $user
     */
    public function mapCommentsToUser(User $user)
    {
        $comments = $this->commentRepository
            ->findCommentsByEmail($user->getEmail());

        foreach ($comments as $comment) {
            $comment->setUser($user);
            $this->commentRepository->save($comment, false);
        }

        $this->commentRepository->flush();
    }

    /**
     * @param int $id
     * @return Comment|null
     */
    public function getCommentById(int $id)
    {
        return $this->commentRepository->find($id);
    }

    /**
     * @param Documentary $documentary
     * @return Comment[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getPublishedCommentsByDocumentary(Documentary $documentary)
    {
        $criteria = new CommentCriteria();
        $criteria->setDocumentary($documentary);
        $criteria->setStatus(CommentStatus::PUBLISHED);

        $comments = $this->getCommentsByCriteria($criteria);
        return $comments;
    }

    /**
     * @param CommentCriteria $criteria
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCommentsByCriteriaQueryBuilder(CommentCriteria $criteria)
    {
        return $this->commentRepository->findCommentsByCriteriaQueryBuilder($criteria);
    }

    /**
     * @param CommentCriteria $criteria
     * @return Comment[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getCommentsByCriteria(CommentCriteria $criteria)
    {
        return $this->commentRepository->findDCommentsByCriteria($criteria);
    }

    /**
     * @param Comment $comment
     */
    public function delete(Comment $comment)
    {
        $this->commentRepository->remove($comment, Sync::YES);

        $commentEvent = new CommentEvent($comment);
        $this->eventDispatcher->dispatch($commentEvent, CommentEvents::COMMENT_DELETED);
    }

    /**
     * @param Comment $comment
     * @throws \Doctrine\ORM\ORMException
     */
    public function createComment(Comment $comment)
    {
        $this->save($comment);

        $commentEvent = new CommentEvent($comment);
        $this->eventDispatcher->dispatch($commentEvent, CommentEvents::COMMENT_CREATED);
    }

    /**
     * @param Comment $comment
     * @param string $updateTimestamps
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Comment $comment, string $updateTimestamps = UpdateTimestamps::YES, string $sync = Sync::YES)
    {
        if ($updateTimestamps === UpdateTimestamps::YES) {
            $currentDateTime = new \DateTime();

            if ($comment->getCreatedAt() == null) {
                $comment->setCreatedAt($currentDateTime);
            } else {
                $comment->setUpdatedAt($currentDateTime);
            }
        }

        $this->commentRepository->save($comment, $sync);
    }
}