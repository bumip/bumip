<?php
namespace Bumip\Helpers;

class StringHelper
{
    public static function replaceDelimited(string $str, string $text, array $delimiter = ['//@begin', '//@end']):string
    {
        $str1 = explode($delimiter[0], $str);
        $str2 = explode($delimiter[1], $str1[1]);
        $strFinal = $str1[0] . $delimiter[0] . $text . $delimiter[1] . $str2[1];
        return $strFinal;
    }
}
