<?php

namespace App\Enum;

class EmailOrderBy
{
    const CREATED_AT = "createdAt";
    const UPDATED_AT = "updatedAt";

    /**
     * @return array
     */
    public static function getAllOrderBys()
    {
        return [
            self::CREATED_AT,
            self::UPDATED_AT
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