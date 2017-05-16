<?php

namespace DW\CommentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use DW\CommentBundle\Enum\CommentStatus;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\UserBundle\Entity\User;
use Gedmo\Timestampable\Traits\Timestampable;

class Comment
{
    use Timestampable;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $status;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Documentary
     */
    private $documentary;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $email;

    /**
     * @var ArrayCollection|CommentVote[]
     */
    private $votes;

    /**
     * @var int
     */
    private $points;

    public function __construct()
    {
        $this->status = CommentStatus::PUBLISH;
        $this->votes = new ArrayCollection();
        $this->points = 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Documentary
     */
    public function getDocumentary()
    {
        return $this->documentary;
    }

    /**
     * @param Documentary $documentary
     */
    public function setDocumentary(Documentary $documentary)
    {
        $this->documentary = $documentary;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints(int $points)
    {
        $this->points = $points;
    }

    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
    }

    /**
     * @param CommentVote $vote
     * @return bool
     */
    public function hasVote(CommentVote $vote)
    {
        return $this->votes->contains($vote);
    }

    /**
     * @param CommentVote $vote
     */
    public function addVote(CommentVote $vote)
    {
        if (!$this->hasVote($vote)) {
            $this->votes->add($vote);
        }
    }
}