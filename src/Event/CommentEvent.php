<?php

namespace App\Event;

use App\Entity\Comment;

class CommentEvent
{
    /**
     * @var Comment
     */
    protected $comment;

    /**
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }
}