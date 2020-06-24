<?php

namespace App\Enum;

class ActivityComponent
{
    const USER = "user";
    const DOCUMENTARY = "documentary";

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        return [
            self::USER,
            self::DOCUMENTARY
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