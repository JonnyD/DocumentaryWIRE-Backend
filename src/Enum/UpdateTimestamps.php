<?php

namespace App\Enum;

class UpdateTimestamps
{
    const YES = "yes";
    const NO = "no";

    /**
     * @return array
     */
    public static function getAllStatuses()
    {
        return [
            self::YES,
            self::NO
        ];
    }

    /**
     * @param string $lookupStatus
     * @return bool
     */
    public static function hasStatus(string $lookupStatus)
    {
        $hasStatus = false;

        foreach (self::getAllStatuses() as $currentStatus) {
            if ($lookupStatus === $currentStatus) {
                $hasStatus = true;
                break;
            }
        }

        return $hasStatus;
    }
}