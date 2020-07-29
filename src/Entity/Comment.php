<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Enum\CommentStatus;
use App\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\Blameable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use App\Traits\Sluggable;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 */
class Comment
{
    use Timestampable;
    use Blameable;
    use Sluggable;
    use SoftDeleteable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Gedmo\Versioned
     */
    private $commentText;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @Gedmo\Versioned
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Documentary", inversedBy="comments")
     * @ORM\JoinColumn(name="documentary_id", referencedColumnName="id")
     * @Gedmo\Versioned
     */
    private $documentary;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getCommentText(): ?string
    {
        return $this->commentText;
    }

    /**
     * @param string $commentText
     * @return Comment
     */
    public function setCommentText(string $commentText): self
    {
        $this->commentText = $commentText;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Comment
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return ($this->status === CommentStatus::PUBLISHED);
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return ($this->status === CommentStatus::PENDING);
    }

    /**
     * @return null|string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param null|string $author
     * @return Comment
     */
    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     * @return Comment
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return Comment
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Documentary|null
     */
    public function getDocumentary(): ?Documentary
    {
        return $this->documentary;
    }

    /**
     * @param Documentary|null $documentary
     * @return Comment
     */
    public function setDocumentary(?Documentary $documentary): self
    {
        $this->documentary = $documentary;

        return $this;
    }
}
