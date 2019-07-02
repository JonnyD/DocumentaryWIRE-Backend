<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CommentRepository;

class CommentService
{
    /**
     * @var CommentRepository
     */
    private $commentRepository;

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
}