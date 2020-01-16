<?php

namespace App\Enum;

class DocumentaryType
{
    const MOVIE = "movie";
    const SERIES = "series";
    const EPISODE = "episode";

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        return [
            self::MOVIE,
            self::SERIES,
            self::EPISODE
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
}