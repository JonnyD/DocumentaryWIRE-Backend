<?php

namespace DW\CommentBundle\Entity;

use DW\UserBundle\Entity\User;
use Gedmo\Timestampable\Traits\Timestampable;

class CommentVote
{
    use Timestampable;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Comment
     */
    private $comment;

    /**
     * @var User
     */
    private $voter;

    /**
     * @var User
     */
    private $votee;

    /**
     * @var int
     */
    private $value;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     */
    public function setComment(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return User
     */
    public function getVoter()
    {
        return $this->voter;
    }

    /**
     * @param User $voter
     */
    public function setVoter(User $voter)
    {
        $this->voter = $voter;
    }

    /**
     * @return User
     */
    public function getVotee()
    {
        return $this->votee;
    }

    /**
     * @param User $votee
     */
    public function setVotee(User $votee)
    {
        $this->votee = $votee;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue(int $value)
    {
        $this->value = $value;
    }
}