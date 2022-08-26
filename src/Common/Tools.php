<?php


namespace Jim\SportsLiveStreamLibrary\Common;


class Tools
{
    public static function camelize($uncamelized_words, $separator = '_')
    {
        if (strpos($uncamelized_words, $separator) === false) {
            return $uncamelized_words;
        }
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
    }

    public static function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    public static function convertArrayUnCamelizeKey($column)
    {
        $newColumn = [];
        foreach ($column as $key => $value) {
            $newKey             = Tools::uncamelize($key);
            $newColumn[$newKey] = $value;
        }
        return $newColumn;
    }

    public static function isContainUpperChar($str)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            if (ord($str[$i]) >= ord('A') &&
                ord($str[$i]) <= ord('Z')) {
                return true;
            }
        }
        return false;
    }

    public static function convertUncamelize2camelize($a)
    {
        $arr = [];
        $del = [];
        foreach ($a as $k => $i) {
            if (strpos($k, '_') !== false) {
                if (is_array($i)) {
                    $i = self::convertUncamelize2camelize($i);
                }
                $arr[Tools::camelize($k)] = $i;
                $del[$k]                  = 1;
            } else {
                if (is_array($i)) {
                    $a[$k] = self::convertUncamelize2camelize($i);
                }
            }
        }
        return array_diff_key($a, $del) + $arr;
    }
}
