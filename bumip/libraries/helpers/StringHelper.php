<?php
namespace Bumip\Helpers;

class StringHelper
{
    /**
     * ReplaceDelimited, check the tests for explanation.
     *
     * @param string $str
     * @param string $text
     * @param array $delimiter
     * @return string
     */
    public static function replaceDelimited(string $str, string $text, array $delimiter = ['//@begin', '//@end']):string
    {
        $str1 = explode($delimiter[0], $str);
        $str2 = explode($delimiter[1], $str1[1]);
        $strFinal = $str1[0] . $delimiter[0] . $text . $delimiter[1] . $str2[1];
        return $strFinal;
    }
    /**
     * A lightweight basic templating function with easy custom delimiters.
     *
     * @param string $content
     * @param array $data
     * @param array $delimiter
     * @return string
     */
    public static function processTemplate(string $content, array $data, array $delimiter = ['[{', '}]']):string
    {
        foreach ($data as $k => $v) {
            if (!is_array($v) && !is_object($v)) {
                $content = str_replace($delimiter[0] . $k . $delimiter[1], $v, $content);
                $content = str_replace($delimiter[0] . " {$k} " . $delimiter[1], $v, $content);
            } elseif (is_object($v)) {
                if (get_class($v) == 'MongoDB\BSON\ObjectId') {
                    $content = str_replace($delimiter[0] . " {$k} " . $delimiter[1], (string) $v, $content);
                }
            }
        }
        return $content;
    }
}
