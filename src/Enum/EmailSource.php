<?php

namespace App\Enum;

class EmailSource
{
    const COMMENT = "comment";
    const USER = "user";
    const WZ_FEEDBURNER = "wz-feedburner";
    const TNS_FEEDBURNER = "tns-feedburner";
    const DW_FEEDBURNER_ACTIVE = "dw-feedburner-active";
    const DW_FEEDBURNER_PENDING = "dw-feedburner-pending";


    /**
     * @return array
     */
    public static function getAllEmailSources()
    {
        return [
            self::COMMENT,
            self::USER,
            self::WZ_FEEDBURNER,
            self::TNS_FEEDBURNER,
            self::DW_FEEDBURNER_ACTIVE,
            self::DW_FEEDBURNER_PENDING
        ];
    }

    /**
     * @param string $lookupEmailSource
     * @return bool
     */
    public static function hasEmailSource(string $lookupEmailSource)
    {
        $hasEmailSource = false;

        foreach (self::getAllEmailSources() as $currentEmailSource) {
            if ($lookupEmailSource === $currentEmailSource) {
                $hasEmailSource = true;
                break;
            }
        }

        return $hasEmailSource;
    }
}