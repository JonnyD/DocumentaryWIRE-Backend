<?php

namespace App\Criteria;

use App\Enum\Subscribed;

class EmailCriteria
{
    /**
     * @var string
     */
    private $subscribed;

    /**
     * @var string
     */
    private $email;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var array
     */
    private $sort;

    /**
     * @return null|string
     */
    public function getSubscribed(): ?string
    {
        return $this->subscribed;
    }

    /**
     * @param string $subscribed
     * @throws \Exception
     */
    public function setSubscribed(string $subscribed): void
    {
        $hasSubscribed = Subscribed::hasStatus($subscribed);
        if (!$hasSubscribed) {
            throw new \Exception('Subscribed status does not exist');
        }

        $this->subscribed = $subscribed;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return array
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param array $sort
     */
    public function setSort(array $sort)
    {
        //@TODO check sort
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }
}