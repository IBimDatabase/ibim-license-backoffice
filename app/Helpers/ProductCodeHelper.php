<?php

namespace App\Helpers;

class ProductCodeHelper
{
    public static function createCodeFromName($productName)
    {
        $array = explode(' ', $productName);
        $code = '';

        forEach($array as $key => $value) {
            if (!in_array($value, [',', '.', '-', '_', '&', '@', '$', '\\', '/']))
            {
                $code .= strtoupper($value) . '_';   
            }
        };

        $productCode = trim($code, '_');
        return $productCode;
    }
}
