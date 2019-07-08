<?php
namespace ICAnalysis\ICPack;

use ICAnalysis\Common;

class ICPack
{

    private $dictDelimiter = '|';

    private $dictNumberPack = null;
    private $dictPackNameKeywordList = array();

    function __construct()
    {
        $dictList = Common::config('pack_dict');
        $this->dictNumberPack = Common::arrayGet($dictList, 'numberPack');
        $this->dictPackNameKeywordList = Common::arrayGet($dictList, 'packNameKeywordList');
    }


    //判断是否是封装
    public function isPack($str,$isFormat=true)
    {
        if ($str) {
            $str = $isFormat?$this->formatPack($str):$str;
            if ($this->isNumberPack($str)) {
                return true;
            }
            if ($this->isLetterPack($str)) {
                return true;
            }
        }
        return false;
    }

    public function isNumberPack($str)
    {
        return Common::isDictExist($this->dictNumberPack, $str,$this->dictDelimiter);
    }

    public function isLetterPack($str)
    {
        if ($this->dictPackNameKeywordList && count($this->dictPackNameKeywordList) >= 2) {
            $pattern1 = "(^[0-9\-]{0,3}({$this->dictPackNameKeywordList[0]}){1}$)";
            $pattern2 = "(^({$this->dictPackNameKeywordList[0]}){1}[0-9\-]{1,6}[A-Za-z]{0,3}$)";
            $pattern3 = "(^[0-9\-]{1,3}({$this->dictPackNameKeywordList[1]}){1}$)";
            $pattern4 = "(^({$this->dictPackNameKeywordList[1]}){1}[0-9\-]{1,6}[A-Za-z]{0,3}$)";
            $pattern = "#{$pattern1}|{$pattern2}|{$pattern3}|{$pattern4}#i";
            if (preg_match($pattern, $str, $matches)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return void
     */
    public function formatPack($value)
    {
        $str=$value;
        //TODO 多个正则匹配时会互串，需单独出来
        $pattern1 = '^('.$this->dictNumberPack.'){1}[_]{0,1}(?:D|I|L|M|R|C|FUSE|LED|SMD|Capacitor|-2){1}$';
        $pattern2 = '^(?:SR|SC|B|D|I|L|LED|M|R|C|FUSE|LED|SMD|Capacitor|CC){1}('.$this->dictNumberPack.'){1}$';
        $pattern3 = '^(?:C|R){1}('.$this->dictNumberPack.'){1}(?:A|S|K){1}$';
        if (preg_match("#^(201|402|603|805)$#", $value, $arr)) {
            if (count($arr) == 2) {
                $str = '0' . $arr[1];
            }
        } elseif (preg_match("#{$pattern1}#i", $value, $arr)
            || preg_match("#{$pattern2}#i", $value, $arr)
            || preg_match("#{$pattern3}#i", $value, $arr)
        ) {
            $pack = Common::arrayGet($arr, 1, '');
            if ($pack) {
                $str = $pack;
            }
        }
        return $str;
    }


}
