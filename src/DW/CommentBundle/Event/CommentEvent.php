<?php

namespace DW\CommentBundle\Event;

use DW\CommentBundle\Entity\Comment;
use Symfony\Component\EventDispatcher\Event;

class CommentEvent extends Event
{
    /**
     * @var Comment
     */
    private $comment;

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