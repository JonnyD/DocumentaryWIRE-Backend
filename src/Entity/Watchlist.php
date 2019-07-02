<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\Blameable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\WatchlistRepository")
 * @Gedmo\Loggable
 */
class Watchlist
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="watchlists")
     * @ORM\JoinColumn(nullable=false)
     * @Gedmo\Versioned
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Documentary", inversedBy="watchlists")
     * @ORM\JoinColumn(nullable=false)
     * @Gedmo\Versioned
     */
    private $documentary;

    public function getId(): ?int
    {
        return $this->id;
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
