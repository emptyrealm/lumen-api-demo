<?php
namespace ICSearch\libs;

/**
 * 公用类
 */

class Common
{

    public static function loadLib($file){
        require_once (dirname(__FILE__) . '/'.$file.'.php');
    }

    public static function loadConfig($file){
        return require (dirname(__FILE__).'/../config/'.$file.'.php');
    }

    public static function loadDict($file){
        return self::loadConfig('dict/'.$file);
    }

    public static function loadCustom($file){
        return require (dirname(__FILE__) . '/../custom/'.$file.'.php');
    }

    #TODO 暂定写法
    public static function config($file,$key=null,$default=null){
        if ($file){
            $list=self::loadConfig($file);
            if ($list && is_array($list)){
                if ($key){
                    return self::arrayGet($list,$key,$default);
                }else{

                    return $list;
                }

            }
        }
        return false;
    }

    #TODO 暂定写法
    public static function custom($file,$key=null,$default=null){
        if ($file){
            $list=self::loadCustom($file);
            if ($list && is_array($list)){
                if ($key){
                    return self::arrayGet($list,$key,$default);
                }else{

                    return $list;
                }

            }
        }
        return false;
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

    public static function partNo($str){
        return strtolower(preg_replace("/[^A-Za-z0-9]/s","",trim($str)));
    }

    public static function partNoLength($str){
        return mb_strlen(self::partNo($str));
    }

    public static function icArrayIunique($array) {
        if (is_array($array)){
            return array_intersect_key(
                $array,
                array_unique(array_map("StrToLower",$array))
            );
        }

    }

    public static function charsetToUtf8($data, $to = 'UTF-8')
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = self::charsetToUtf8($val, $to);
            }
        } else {
            $encode_array = array('ASCII', 'UTF-8', 'GBK', 'GB2312', 'BIG5');
            $encoded = mb_detect_encoding($data, $encode_array);
            $to = strtoupper($to);
            if ($encoded != $to) {
                $data = mb_convert_encoding($data, $to, $encoded);
            }
        }
        return $data;
    }

    /**
     * 是否包含中文
     *
     * @param [type] $str
     * @return boolean
     */
    public static function hasChinese($str)
    {
        return preg_match("#[\x{4e00}-\x{9fa5}]+#u", $str)?true:false;
    }

    /**
     * 是否包含中文
     *
     * @param [type] $str
     * @return boolean
     */
    public static function hasEnglish($str)
    {
        return preg_match("#[A-Za-z]+#", $str)?true:false;
    }


    /**
     * 是否包含数字
     *
     * @param [type] $str
     * @return boolean
     */
    public static function hasNumber($str)
    {
        return preg_match("#[0-9]+([.]{1}[0-9]+){0,1}#", $str)?true:false;
    }

    /**
     * 判断是否是英文
     *
     * @param [type] $str
     * @return boolean
     */
    public static function isEnglish($str){
        return preg_match("#^([A-Za-z]+)$#", $str)?true:false;
    }


    /**
     * 获取高亮型号数组
     * @param $highlightList
     * @return mixed
     */
    public static function highlightPartList($highlightList){
        if (!empty($highlightList['part_no.full'])) {
            $list = $highlightList['part_no.full'];
        } else if (!empty($highlightList['part_no.extra'])) {
            $list = $highlightList['part_no.extra'];
        }else{
            $list=self::arrayGet($highlightList,'part_no',array());
        }
        return $list;
    }

    /**
     * 获取第一个高亮型号
     * @param $highlightList
     * @param null $default
     * @return mixed|null
     */
    public static function firstHighlightPart($highlightList,$default=null){
        $list=self::highlightPartList($highlightList);
        return Common::arrayGet($list,0,$default);
    }

    /**
     * 获取第一个高亮型号通过列表
     * @param $highlightList
     * @param $id
     * @param null $default
     * @return mixed|null
     */

    public static function firstHighlightPartByList($highlightList,$id,$default=null){
        $highlight=self::arrayGet($highlightList,$id,array());
        return self::firstHighlightPart($highlight,$default);
    }


    public static function getKeywordByAnalysisResult($list){
        $res=[];
        if (!empty($list)) {
            foreach ($list as $vv) {
                if (!empty($vv['w']) && $vv['t'] != 3) {
                    $res[] = $vv['w'];
                }
            }
        }
        return $res;
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


        /**
     * 关联数组合并
     *
     * @param [array] $a
     * @param [array] $b
     * @return void
     */
    static function arrayMerge(&$a,array $b){
        foreach($a as $key=>&$val){
            if(is_array($val) && array_key_exists($key, $b) && is_array($b[$key])){
                self::arrayMerge($val,$b[$key]);
                $val = $val + $b[$key];
            }else if(is_array($val) || (array_key_exists($key, $b) && is_array($b[$key]))){
                $val = is_array($val)?$val:$b[$key];
            }
        }
        $a = $a + $b;
    }




}