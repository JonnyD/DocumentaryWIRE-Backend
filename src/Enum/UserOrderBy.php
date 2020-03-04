<?php

namespace App\Enum;

class UserOrderBy
{
    const ENABLED = "enabled";
    const LAST_LOGIN = "lastLogin";

    /**
     * @return array
     */
    public static function getAllOrderBys()
    {
        return [
            self::ENABLED,
            self::LAST_LOGIN
        ];
    }

    /**
     * @param string $lookupOrderBy
     * @return bool
     */
    public static function hasOrderBy(string $lookupOrderBy)
    {
        $hasOrderBy = false;

        foreach (self::getAllOrderBys() as $currentOrderBy) {
            if ($lookupOrderBy === $currentOrderBy) {
                $hasOrderBy = true;
                break;
            }
        }

        return $hasOrderBy;
    }
}