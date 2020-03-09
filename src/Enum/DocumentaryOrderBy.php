<?php

namespace App\Enum;

class DocumentaryOrderBy
{
    const CREATED_AT = "createdAt";
    const UPDATED_AT = "updatedAt";
    const VIEWS = "views";
    const COMMENT_COUNT = "commentCount";
    const WATCHLIST_COUNT = "watchlistCount";
    const YEAR_FROM = "yearFrom";
    const YEAR_TO = "yearFrom";
    const TODAY_VIEWS = "todayViews";

    /**
     * @return array
     */
    public static function getAllOrderBys()
    {
        return [
            self::CREATED_AT,
            self::UPDATED_AT,
            self::VIEWS,
            self::COMMENT_COUNT,
            self::WATCHLIST_COUNT,
            self::YEAR_FROM,
            self::YEAR_TO,
            self::TODAY_VIEWS
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