<?php
namespace ICAnalysis;

/**
 * 公用类
 */

class Common
{

    public static function config($file){
        return require (dirname(__FILE__).'/config/'.$file.'.php');
    }

    public static function dd($data){
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit();
    }

    /**
     * @param $array
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public static  function arrayGet($array, $key, $default = null){
        if (! is_array($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) &&  isset($array[$segment])) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }
        return $array;
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

    static function isDictExist($haystack, $needle, $delimiter = '|')
    {
        return self::dictExist($haystack, $needle, $delimiter) !== false ? true : false;
    }



}