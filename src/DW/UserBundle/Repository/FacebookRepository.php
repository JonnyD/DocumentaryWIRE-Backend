<?php

namespace DW\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use DW\UserBundle\Entity\Facebook;

class FacebookRepository extends EntityRepository
{
    /**
     * @param Facebook $facebook
     * @param bool $sync
     */
    public function save(Facebook $facebook, bool $sync = true)
    {
        $this->getEntityManager()->persist($facebook);
        if ($sync) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}