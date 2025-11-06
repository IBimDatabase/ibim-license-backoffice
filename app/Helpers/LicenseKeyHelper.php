<?php

namespace App\Helpers;

class LicenseKeyHelper
{
    public static function create()
    {
        return sprintf('%04X-%04X-%04X-%04X',
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0C2f ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0x2Aff )
        );
    }

    public static function licenseKeyHash($licenseKey)
    {
        $subLicenseKey = substr($licenseKey, 4, 11);
        return str_replace($subLicenseKey, '-****-****-', $licenseKey);
    }
}
