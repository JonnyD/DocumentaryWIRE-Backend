<?php

namespace App\Enum;

class CommentStatus
{
    const PUBLISHED = "published";
    const PENDING = "pending";
    const REJECTED = "rejected";

    public static function getAllStatuses()
    {
        return [
            self::PUBLISHED,
            self::PENDING,
            self::REJECTED
        ];
    }

    /**
     * @param string $status
     * @return bool
     */
    public static function hasStatus(string $status)
    {
        return in_array($status, self::getAllStatuses());
    }
}