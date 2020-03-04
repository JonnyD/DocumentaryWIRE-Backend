<?php

namespace App\Enum;

class ActivityOrderBy
{
    const CREATED_AT = "createdAt";
    const GROUP_NUMBER = "groupNumber";


    /**
     * @return array
     */
    public static function getAllOrderBys()
    {
        return [
            self::CREATED_AT,
            self::GROUP_NUMBER
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