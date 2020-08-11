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
     * @var string
     */
    private $isRoleAdmin;

    /**
     * @param Comment $comment
     * @param bool $isRoleAdmin
     */
    public function __construct(
        Comment $comment,
        bool $isRoleAdmin)
    {
        $this->comment = $comment;
        $this->isRoleAdmin = $isRoleAdmin;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->comment->getId(),
            'commentText' => $this->comment->getCommentText(),
            'author' => $this->comment->getAuthor(),
            'createdAt' => $this->comment->getCreatedAt(),
            'updatedAt' => $this->comment->getUpdatedAt(),
            'status' => $this->comment->getStatus()
        ];

        if ($this->isRoleAdmin) {
            $array['email'] = $this->comment->getEmail();
        }

        if ($this->comment->getUser() != null) {
            $array['user'] = [
                'id' => $this->comment->getUser()->getId(),
                'username' => $this->comment->getUser()->getUsername()
            ];
        }

        if ($this->comment->getDocumentary() != null) {
            $array['documentary'] = [
                'id' => $this->comment->getDocumentary()->getId(),
                'title' => $this->comment->getDocumentary()->getTitle(),
                'type' => $this->comment->getDocumentary()->getType(),
                'slug' => $this->comment->getDocumentary()->getSlug()
            ];
        }

        return $array;
    }

    public function toObject(array $data)
    {
        // TODO: Implement toObject() method.
    }
}