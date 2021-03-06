<?php

namespace App\Entity;

use App\Enum\UserStatus;
use App\Traits\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Blameable\Traits\Blameable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use App\Traits\Sluggable;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Table(name="user", indexes={
 *     @ORM\Index(name="search_idx_username", columns={"username"}),
 *     @ORM\Index(name="search_idx_email", columns={"email"}),
 * })
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(fields={"username"}, message="USERNAME_IS_ALREADY_IN_USE")
 * @UniqueEntity(fields={"email"}, message="EMAIL_IS_ALREADY_IN_USE")
 *
 * @Gedmo\Loggable
 */
class User extends BaseUser
{
    use Timestampable;
    use Blameable;
    use SoftDeleteable;

    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_USER = "ROLE_USER";

    /**
     * To validate supported roles
     *
     * @var array
     */
    static public $ROLES_SUPPORTED = [
        self::ROLE_ADMIN => self::ROLE_ADMIN,
        self::ROLE_USER => self::ROLE_USER,
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="FIELD_CAN_NOT_BE_EMPTY")
     * @Groups({"user:write"})
     */
    protected $email;

    /**
     * @Groups({"user:read", "user:write"})
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "FIELD_LENGTH_TOO_SHORT",
     *      maxMessage = "FIELD_LENGTH_TOO_LONG"
     * )
     * @Gedmo\Versioned
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "FIELD_LENGTH_TOO_SHORT",
     *      maxMessage = "FIELD_LENGTH_TOO_LONG"
     * )
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $aboutMe;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetKey;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user")
     */
    private $comments;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $commentCount;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Watchlist", mappedBy="user")
     */
    private $watchlists;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $watchlistCount;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="user")
     */
    private $activities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SocialAccount", mappedBy="user")
     */
    private $socialAccounts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Documentary", mappedBy="user")
     */
    private $documentaries;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Follow", mappedBy="userFrom", orphanRemoval=true)
     */
    private $followFrom;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $followFromCount;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Follow", mappedBy="userTo", orphanRemoval=true)
     */
    private $followTo;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $followToCount;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->watchlists = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->socialAccounts = new ArrayCollection();
        $this->documentaries = new ArrayCollection();
        $this->followFrom = new ArrayCollection();
        $this->followTo = new ArrayCollection();
        $this->enabled = false;
        $this->commentCount = 0;
        $this->watchlistCount = 0;
        $this->followFromCount = 0;
        $this->followToCount = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return null|String
     */
    public function getName(): ?String
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAboutMe()
    {
        return $this->aboutMe;
    }

    /**
     * @param string $aboutMe
     */
    public function setAboutMe(string $aboutMe)
    {
        $this->aboutMe = $aboutMe;
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
     * @return int
     */
    public function getCommentCount(): ?int
    {
        return $this->commentCount;
    }

    /**
     * @param int $commentCount
     */
    public function setCommentCount(int $commentCount)
    {
        $this->commentCount = $commentCount;
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
     * @return int
     */
    public function getWatchlistCount(): ?int
    {
        return $this->watchlistCount;
    }

    /**
     * @param int $watchlistCount
     */
    public function setWatchlistCount(int $watchlistCount)
    {
        $this->watchlistCount = $watchlistCount;
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

    /**
     * @return Collection|SocialAccount[]
     */
    public function getSocialAccounts(): Collection
    {
        return $this->socialAccounts;
    }

    /**
     * @param SocialAccount $socialAccount
     * @return User
     */
    public function addSocialAccount(SocialAccount $socialAccount): self
    {
        if (!$this->socialAccounts->contains($socialAccount)) {
            $this->socialAccounts[] = $socialAccount;
            $socialAccount->setUser($this);
        }

        return $this;
    }

    /**
     * @param SocialAccount $socialAccount
     * @return User
     */
    public function removeSocialAccount(SocialAccount $socialAccount): self
    {
        if ($this->socialAccounts->contains($socialAccount)) {
            $this->socialAccounts->removeElement($socialAccount);
            // set the owning side to null (unless already changed)
            if ($socialAccount->getUser() === $this) {
                $socialAccount->setUser(null);
            }
        }

        return $this;
    }

    public function getAvatar(): ?string
    {
        $avatar = $this->avatar;

        if ($avatar === null) {
            $avatar = 'default.jpg';
        }

        return $avatar;
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

    public function getActivatedAt(): ?\DateTimeInterface
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(?\DateTimeInterface $activatedAt): self
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActivated()
    {
        return ($this->getActivatedAt() !== null);
    }

    /**
     * @return Collection|Documentary[]
     */
    public function getDocumentaries(): Collection
    {
        return $this->documentaries;
    }

    /**
     * @param Documentary $documentary
     * @return User
     */
    public function addDocumentary(Documentary $documentary): self
    {
        if (!$this->documentaries->contains($documentary)) {
            $this->documentaries[] = $documentary;
            $documentary->setAddedBy($this);
        }

        return $this;
    }

    /**
     * @param Documentary $documentary
     * @return User
     */
    public function removeDocumentary(Documentary $documentary): self
    {
        if ($this->documentaries->contains($documentary)) {
            $this->documentaries->removeElement($documentary);
            // set the owning side to null (unless already changed)
            if ($documentary->getAddedBy() === $this) {
                $documentary->setAddedBy(null);
            }
        }

        return $this;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function isGranted(string $role)
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @return Collection|Follow[]
     */
    public function getFollowFrom(): Collection
    {
        return $this->followFrom;
    }

    /**
     * @param Follow $followFrom
     * @return User
     */
    public function addFollowFrom(Follow $followFrom): self
    {
        if (!$this->followFrom->contains($followFrom)) {
            $this->followFrom[] = $followFrom;
            $followFrom->setUserFrom($this);
        }

        return $this;
    }

    /**
     * @param Follow $followFrom
     * @return User
     */
    public function removeFollowFrom(Follow $followFrom): self
    {
        if ($this->followFrom->contains($followFrom)) {
            $this->subscribedFrom->removeElement($followFrom);
            // set the owning side to null (unless already changed)
            if ($followFrom->getUserFrom() === $this) {
                $followFrom->setUserFrom(null);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getFollowFromCount(): int
    {
        return $this->followFromCount;
    }

    /**
     * @param int $followFromCount
     */
    public function setFollowFromCount(int $followFromCount)
    {
        $this->followFromCount = $followFromCount;
    }

    /**
     * @return Collection|Follow[]
     */
    public function getFollowTo(): Collection
    {
        return $this->followTo;
    }

    /**
     * @param Follow $followTo
     * @return User
     */
    public function addFollowTo(Follow $followTo): self
    {
        if (!$this->followTo->contains($followTo)) {
            $this->followTo[] = $followTo;
            $followTo->setUserTo($this);
        }

        return $this;
    }

    /**
     * @param Follow $followTo
     * @return User
     */
    public function removeFollowTo(Follow $followTo): self
    {
        if ($this->followTo->contains($followTo)) {
            $this->followTo->removeElement($followTo);
            // set the owning side to null (unless already changed)
            if ($followTo->getUserTo() === $this) {
                $followTo->setUserTo(null);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getFollowToCount(): int
    {
        return $this->followToCount;
    }

    /**
     * @param int $followToCount
     */
    public function setFollowToCount(int $followToCount)
    {
        $this->followToCount = $followToCount;
    }
}
