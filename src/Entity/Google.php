<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GoogleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Google extends SocialAccount
{

}