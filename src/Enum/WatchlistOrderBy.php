<?php

namespace App\Enum;

class WatchlistOrderBy
{
    const USER_ID = 'userId';
    const DOCUMENTARY_ID = 'documentaryId';
    const CREATED_AT = 'createdAt';

    /**
     * @return array
     */
    public static function getAllOrderBys()
    {
        return [
            self::USER_ID,
            self::DOCUMENTARY_ID,
            self::CREATED_AT
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