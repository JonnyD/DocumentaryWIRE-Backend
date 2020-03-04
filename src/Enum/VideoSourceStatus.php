<?php

namespace App\Enum;

class VideoSourceStatus
{
    const ENABLED = "enabled";
    const DISABLED = "disabled";

    /**
     * @return array
     */
    public static function getAllStatuses()
    {
        return [
            self::ENABLED,
            self::DISABLED
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