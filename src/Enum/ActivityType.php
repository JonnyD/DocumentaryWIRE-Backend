<?php

namespace App\Enum;

class ActivityType
{
    const WATCHLIST = "watchlist";
    const COMMENT = "comment";
    const FOLLOW = "follow";
    const JOINED = "joined";
    const ADDED = "added";

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        return [
            self::WATCHLIST,
            self::ADDED,
            self::JOINED,
            self::COMMENT,
            self::FOLLOW
        ];
    }

    /**
     * @return array
     */
    public static function getTypesWithChildren()
    {
        return [
            self::WATCHLIST,
            self::JOINED,
            self::ADDED
        ];
    }

    /**
     * @param string $lookupType
     * @return bool
     */
    public static function hasType(string $lookupType)
    {
        $hasType = false;

        foreach (self::getAllTypes() as $currentType) {
            if ($lookupType === $currentType) {
                $hasType = true;
                break;
            }
        }

        return $hasType;
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function hasChildren(string $type)
    {
        return in_array($type, self::getTypesWithChildren());
    }
}