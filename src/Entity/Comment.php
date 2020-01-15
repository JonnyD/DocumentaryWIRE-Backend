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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentText(): ?string
    {
        return $this->commentText;
    }

    public function setCommentText(string $commentText): self
    {
        $this->commentText = $commentText;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

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
        return ($this->status === CommentStatus::PUBLISH);
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return ($this->status === CommentStatus::PENDING);
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDocumentary(): ?Documentary
    {
        return $this->documentary;
    }

    public function setDocumentary(?Documentary $documentary): self
    {
        $this->documentary = $documentary;

        return $this;
    }
}
