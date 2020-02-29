<?php

namespace App\Enum;

class CommentStatus
{
    const PUBLISHED = "published";
    const PENDING = "pending";

    public static function getAllStatuses()
    {
        return [
            self::PUBLISHED,
            self::PENDING
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