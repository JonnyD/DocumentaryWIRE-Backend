<?php

namespace App\Entity;

use App\Enum\UserStatus;
use App\Traits\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Blameable\Traits\Blameable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use App\Traits\Sluggable;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ApiResource(
 *     collectionOperations={
 *      "get","post",
 *      "collName_api_me"={"route_name"="api_me"}
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"})
 * @UniqueEntity(fields={"username"})
 * @Gedmo\Loggable
 */
class User implements UserInterface
{
    use Timestampable;
    use Blameable;
    use SoftDeleteable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Gedmo\Versioned
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Gedmo\Versioned
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Gedmo\Versioned
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetKey;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resetRequestAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastResetAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $activationKey;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastActiveAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Watchlist", mappedBy="user")
     */
    private $watchlists;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="user")
     */
    private $activities;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->watchlists = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->status = UserStatus::INACTIVE;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Watchlist[]
     */
    public function getWatchlists(): Collection
    {
        return $this->watchlists;
    }

    public function addWatchlist(Watchlist $watchlist): self
    {
        if (!$this->watchlists->contains($watchlist)) {
            $this->watchlists[] = $watchlist;
            $watchlist->setUser($this);
        }

        return $this;
    }

    public function removeWatchlist(Watchlist $watchlist): self
    {
        if ($this->watchlists->contains($watchlist)) {
            $this->watchlists->removeElement($watchlist);
            // set the owning side to null (unless already changed)
            if ($watchlist->getUser() === $this) {
                $watchlist->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Activity[]
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->activities->contains($activity)) {
            $this->activities[] = $activity;
            $activity->setUser($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->contains($activity)) {
            $this->activities->removeElement($activity);
            // set the owning side to null (unless already changed)
            if ($activity->getUser() === $this) {
                $activity->setUser(null);
            }
        }

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getResetKey(): ?string
    {
        return $this->resetKey;
    }

    public function setResetKey(?string $resetKey): self
    {
        $this->resetKey = $resetKey;

        return $this;
    }

    public function getResetRequestAt(): ?\DateTimeInterface
    {
        return $this->resetRequestAt;
    }

    public function setResetRequestAt(?\DateTimeInterface $resetRequestAt): self
    {
        $this->resetRequestAt = $resetRequestAt;

        return $this;
    }

    public function getLastResetAt(): ?\DateTimeInterface
    {
        return $this->lastResetAt;
    }

    public function setLastResetAt(?\DateTimeInterface $lastResetAt): self
    {
        $this->lastResetAt = $lastResetAt;

        return $this;
    }

    public function getActivatedAt(): ?\DateTimeInterface
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(?\DateTimeInterface $activatedAt): self
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    public function getActivationKey(): ?string
    {
        return $this->activationKey;
    }

    public function setActivationKey(?string $activationKey): self
    {
        $this->activationKey = $activationKey;

        return $this;
    }

    public function getLastActiveAt(): ?\DateTimeInterface
    {
        return $this->lastActiveAt;
    }

    public function setLastActiveAt(?\DateTimeInterface $lastActiveAt): self
    {
        $this->lastActiveAt = $lastActiveAt;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
