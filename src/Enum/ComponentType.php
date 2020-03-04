<?php

namespace App\Enum;

class ComponentType
{
    const DOCUMENTARY = "documentary";
    const USER = "user";

    /**
     * @return array
     */
    public static function getAllOrderBys()
    {
        return [
            self::DOCUMENTARY,
            self::USER
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