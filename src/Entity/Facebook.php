<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FacebookRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Facebook extends SocialAccount
{

}