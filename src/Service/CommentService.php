<?php

namespace App\Service;

use App\Criteria\CommentCriteria;
use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;

class CommentService
{
    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * @param CommentRepository $commentRepository
     */
    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
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
     * @param bool $sync
     */
    public function save(Comment $comment, $sync = true)
    {
        if ($comment->getCreatedAt() == null) {
            $comment->setCreatedAt(new \DateTime());
        } else {
            $comment->setUpdatedAt(new \DateTime());
        }

        $this->commentRepository->save($comment, $sync);
    }
}