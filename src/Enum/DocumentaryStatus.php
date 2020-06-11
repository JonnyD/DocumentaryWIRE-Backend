<?php

namespace App\Enum;

class DocumentaryStatus
{
    const PUBLISHED = "published";
    const REJECTED = "rejected";
    const PENDING = "pending";
    const DRAFT = "draft";

    /**
     * @return array
     */
    public static function getAllStatuses()
    {
        return [
            self::PUBLISHED,
            self::REJECTED,
            self::PENDING,
            self::DRAFT
        ];
    }

    /**
     * @param string $lookupStatus
     * @return bool
     */
    public static function hasStatus(string $lookupStatus)
    {
        $hasStatus = false;

        foreach (self::getAllStatuses() as $currentStatus) {
            if ($lookupStatus === $currentStatus) {
                $hasStatus = true;
                break;
            }
        }

        return $hasStatus;
    }
}