<?php

namespace ICSearch\libs;

/**
 * 字典
 */

class DictHelp
{

    const DELIMITER='|';


    static function isDictExist($haystack, $needle, $delimiter = self::DELIMITER)
    {
        return self::dictExist($haystack, $needle, $delimiter) !== false ? true : false;
    }


        /**
     * 返回mb_stripos结果，会有返回0的情况
     * @param $haystack
     * @param $needle
     * @param null $delimiter
     * @return bool|int
     */
    static function dictExist($haystack, $needle, $delimiter = '|')
    {
        if ($haystack && $needle) {
            return mb_stripos($delimiter . $haystack . $delimiter, $delimiter . $needle . $delimiter);
        }
        return false;
    }




}
