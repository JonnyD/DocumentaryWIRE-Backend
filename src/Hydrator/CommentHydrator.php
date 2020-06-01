<?php

namespace App\Hydrator;

use App\Entity\Comment;

class CommentHydrator implements HydratorInterface
{
    /**
     * @var Comment
     */
    private $comment;

    /**
     * @param Comment $comment
     */
    public function __construct(
        Comment $comment)
    {
        $this->comment = $comment;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->comment->getId(),
            'commentText' => $this->comment->getCommentText(),
            'author' => $this->comment->getAuthor(),
            'createdAt' => $this->comment->getCreatedAt(),
            'updatedAt' => $this->comment->getUpdatedAt()
        ];

        if ($this->comment->getUser() != null) {
            $array['user'] = [
                'id' => $this->comment->getUser()->getId(),
                'username' => $this->comment->getUser()->getUsername()
            ];
        }

        if ($this->comment->getDocumentary() != null) {
            $array['documentary'] = [
                'id' => $this->comment->getDocumentary()->getId(),
                'title' => $this->comment->getDocumentary()->getTitle()
            ];
        }

        return $array;
    }
}