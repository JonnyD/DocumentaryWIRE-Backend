<?php

namespace DW\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use DW\ActivityBundle\Entity\Activity;
use DW\CommentBundle\Entity\Comment;
use DW\CommentBundle\Entity\CommentVote;
use DW\WatchlistBundle\Entity\Watchlist;
use Gedmo\Timestampable\Traits\Timestampable;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    use Timestampable;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $salt;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $avatar;

    /**
     * @var string
     */
    private $resetKey;

    /**
     * @var \DateTime
     */
    private $resetRequestAt;

    /**
     * @var \DateTime
     */
    private $lastResetAt;

    /**
     * @var \DateTime
     */
    private $activatedAt;

    /**
     * @var string
     */
    private $activationKey;

    /**
     * @var \DateTime
     */
    private $lastActiveAt;

    /**
     * @var int
     */
    private $status;

    /**
     * @var Facebook
     */
    private $facebook;

    /**
     * @var ArrayCollection|Comment[]
     */
    private $comments;

    /**
     * @var ArrayCollection|Role[]
     */
    private $roles;

    /**
     * @var ArrayCollection|Watchlist[]
     */
    private $watchlisted;

    /**
     * @var ArrayCollection|Activity[]
     */
    private $activity;

    /**
     * @var ArrayCollection|CommentVote[]
     */
    private $commentVoteGiven;

    /**
     * @var ArrayCollection|CommentVote[]
     */
    private $commentVoteReceived;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->watchlisted = new ArrayCollection();
        $this->activity = new ArrayCollection();
        $this->commentVoteGiven = new ArrayCollection();
        $this->commentVoteReceived = new ArrayCollection();
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
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @param string $salt
     */
    public function setSalt(string $salt)
    {
        $this->salt = $salt;
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
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar(string $avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return string
     */
    public function getResetKey()
    {
        return $this->resetKey;
    }

    /**
     * @param string $resetKey
     */
    public function setResetKey(string $resetKey)
    {
        $this->resetKey = $resetKey;
    }

    /**
     * @return \DateTime
     */
    public function getResetRequestAt()
    {
        return $this->resetRequestAt;
    }

    /**
     * @param \DateTime $resetRequestAt
     */
    public function setResetRequestAt(\DateTime $resetRequestAt)
    {
        $this->resetRequestAt = $resetRequestAt;
    }

    /**
     * @return \DateTime
     */
    public function getLastResetAt()
    {
        return $this->lastResetAt;
    }

    /**
     * @param \DateTime $lastResetAt
     */
    public function setLastResetAt(\DateTime $lastResetAt)
    {
        $this->lastResetAt = $lastResetAt;
    }

    /**
     * @return \DateTime
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * @param \DateTime $activatedAt
     */
    public function setActivatedAt(\DateTime $activatedAt)
    {
        $this->activatedAt = $activatedAt;
    }

    /**
     * @return string
     */
    public function getActivationKey()
    {
        return $this->activationKey;
    }

    /**
     * @param string $activationKey
     */
    public function setActivationKey(string $activationKey)
    {
        $this->activationKey = $activationKey;
    }

    /**
     * @return \DateTime
     */
    public function getLastActiveAt()
    {
        return $this->lastActiveAt;
    }

    /**
     * @param \DateTime $lastActiveAt
     */
    public function setLastActiveAt(\DateTime $lastActiveAt)
    {
        $this->lastActiveAt = $lastActiveAt;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * @return Facebook
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param Facebook $facebook
     */
    public function setFacebook(Facebook $facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param ArrayCollection|Comment[] $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @param Comment $comment
     * @return bool
     */
    public function hasComment(Comment $comment)
    {
        return $this->comments->contains($comment);
    }

    /**
     * @param Comment $comment
     */
    public function addComment(Comment $comment)
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

    /**
     * @return ArrayCollection|Role[]
     */
    public function getRoles()
    {
        $roleNames = [];
        foreach ($this->roles as $role) {
            $roleNames[] = $role->getRole();
        }
        return $roleNames;
    }

    /**
     * @param ArrayCollection|Role[] $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param Role $role
     * @return bool
     */
    public function hasRole(Role $role)
    {
        return $this->roles->contains($role);
    }

    /**
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * @return ArrayCollection|Watchlist[]
     */
    public function getWatchlisted()
    {
        return $this->watchlisted;
    }

    /**
     * @param array $watchlisted
     */
    public function setWatchlisted($watchlisted)
    {
        $this->watchlisted = $watchlisted;
    }

    /**
     * @param Watchlist $watchlist
     * @return bool
     */
    public function hasWatchlist(Watchlist $watchlist)
    {
        return $this->watchlisted->contains($watchlist);
    }

    /**
     * @param Watchlist $watchlist
     */
    public function addWatchlist(Watchlist $watchlist)
    {
        if (!$this->hasWatchlist($watchlist)) {
            $this->watchlisted->add($watchlist);
        }
    }

    /**
     * @return ArrayCollection|Activity[]
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param array $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
    }

    /**
     * @param Activity $activity
     * @return bool
     */
    public function hasActivity(Activity $activity)
    {
        return $this->activity->contains($activity);
    }

    /**
     * @param Activity $activity
     */
    public function addActivity(Activity $activity)
    {
        if (!$this->hasActivity($activity)) {
            $this->activity->add($activity);
        }
    }

    /**
     * @return ArrayCollection|CommentVote[]
     */
    public function getCommentsVotesGiven()
    {
        return $this->commentVoteGiven;
    }

    /**
     * @param $commentsVotesGiven
     */
    public function setCommentsVotesGiven($commentsVotesGiven)
    {
        $this->commentsVotesGiven = $commentsVotesGiven;
    }

    /**
     * @param CommentVote $commentVote
     * @return bool
     */
    public function hasCommentVoteGiven(CommentVote $commentVote)
    {
        return $this->commentVoteGiven->contains($commentVote);
    }

    /**
     * @param CommentVote $commentVote
     */
    public function addCommentVoteGiven(CommentVote $commentVote)
    {
        if (!$this->hasCommentVoteGiven($commentVote)) {
            $this->commentVoteGiven->add($commentVote);
        }
    }

    /**
     * @return ArrayCollection|CommentVote[]
     */
    public function getCommentVoteReceived()
    {
        return $this->commentVoteReceived;
    }

    /**
     * @param $commentVoteReceived
     */
    public function setCommentVoteReceived($commentVoteReceived)
    {
        $this->commentsVoteReceived = $commentVoteReceived;
    }

    /**
     * @param CommentVote $commentVote
     * @return bool
     */
    public function hasCommentVoteReceived(CommentVote $commentVote)
    {
        return $this->commentVoteReceived->contains($commentVote);
    }

    public function addCommentVoteReceived(CommentVote $commentVote)
    {
        if (!$this->hasCommentVoteReceived($commentVote)) {
            $this->addCommentVoteReceived($commentVote);
        }
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        //@TODO
    }
}