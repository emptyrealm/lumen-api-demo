<?php
namespace ICAnalysis\ICSynonym;

use ICAnalysis\Common;

class ICSynonym
{


    private $dictDelimiter = '|';
    private $dictSynonymList = array();
    private $dictUnitSynonymList = array();

    function __construct()
    {
        $this->loadDict();
    }

    private function loadDict(){
        if(!$this->dictSynonymList){
            $dictList = Common::config('synonym');
            $dictNumberPackSynonym = Common::arrayGet($dictList, 'numberPackSynonym');
            $dictSynonym = Common::arrayGet($dictList, 'cateSynonym') . $this->dictDelimiter
            . Common::arrayGet($dictList, 'mfrSynonym') .
            $this->dictDelimiter . $dictNumberPackSynonym;
            $this->dictSynonymList = explode($this->dictDelimiter, $dictSynonym);
            $this->dictUnitSynonymList = !empty($dictList['unitSynonym']) ? explode($this->dictDelimiter, $dictList['unitSynonym']) : '';
        }
    }

    public function getKeywordSynonym(array $list)
    {
        $t=[];
        if ($list){
            foreach ($list as $v){
                $tSynonymList = $this->getSynonym($v);
                $t[$v]=[
                    'str' => $v,
                    'synonym' => $tSynonymList
                ];
            }
        }
        return $t;
    }


    /**
     * #TODO 特殊符号的同义词 % per
     * 获取该词的同义词
     * @param $str
     * @return array
     */

    public function getSynonym($str)
    {
        $str = $this->synonymStr($str);
        $res = array();
        foreach ($this->dictSynonymList as $v) {
            if (Common::isDictExist($v, $str, ',') !== false) {
                $res = explode(",", trim($v, ','));
                break;
            }
        }
        if (!$res) {
            if ($this->dictUnitSynonymList && is_array($this->dictUnitSynonymList)){
                foreach ($this->dictUnitSynonymList as $vv) {
                    if (preg_match('#^([0-9.]+)(' . str_replace(",", "|", $vv) . '){1}$#', $str, $arr)) {
                        $t_res = explode(",", trim($vv, ','));
                        if (count($arr) == 3 && is_array($t_res)) {
                            foreach ($t_res as $v) {
                                $res[] = $arr[1] . $v;
                            }
                        }
                        break;
                    }
                }
            }

        }
        $k = array_search($str, $res);
        if ($k !== false) {
            array_splice($res, $k, 1);
        }
        return $res;
    }


    public function synonymStr($str)
    {
        $str = $this->unitConvert($str);
        $str = $this->replaceStr($str);
        return trim(strtolower($str));
    }

    public function unitConvert($keyword)
    {
        $search = [
            "1/2W", "1/4W", "1/8W", "1/10W",
            "1/16W", "1/20W",
            "1/2 W", "1/4 W", "1/8 W", "1/10 W",
            "1/16 W", "1/20 W",
        ];
        $replace = [
            "0.5W", "0.25W", "0.125W", "0.1W",
            "0.063W", "0.05W",
            "0.5W", "0.25W", "0.125W", "0.1W",
            "0.063W", "0.05W",
        ];
        $keyword = str_ireplace($search, $replace, $keyword);
        return $keyword;
    }

        /**
     * 替换特殊字符
     * @param $keyword
     * @return mixed
     */
    public function replaceStr($keyword)
    {
        $search = [
            "%", "°", "µ", "Ω", "ppm/℃", "℃", "μ", "％",
            "，", ",",
            "：", " :", ": ",
            "）", "（",
            "±", "+/-", "/-",
            "@", "=",'+'
        ];
        $replace = [
            "per ", "deg ", "u", "ohms ", "ppm ", "cel ", "u", "per ",
            " , ", " , ",
            ":", ":", ":",
            ")", "(",
            " ", " ", " ",
            " ", " = "," "
        ];
        $keyword = str_ireplace($search, $replace, $keyword);
        return $keyword;
    }


    public function equalBySynonymList(array $list)
    {
        if ($list) {
            foreach ($list as $k => $v) {
                if (!is_array($v)) {
                    $list[$k] = $this->synonymStr($v);
                }
            }
        }

        return $list;
    }

      /**
     * 判断一个字符（包括同义词）是否在一个数组里
     * @param $str
     * @param array $list
     * @return bool
     */
    public function inArrayBySynonym($str, array $list)
    {
        if ($str && $list) {
            $t_arr = $this->getSynonym($str);
            $t_arr[] = $str;
            if ($t_arr && $list) {
                foreach ($t_arr as $tt) {
                    foreach ($list as $ttt) {
                        $ttt = $this->synonymStr($ttt);
                        if (strtolower($tt) == strtolower($ttt)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function synonymIunique($list)
    {
        $t_list = [];
        $t_pass_list = [];
        $list = explode(",", strtolower(implode(",", $list)));
        if ($list) {
            foreach ($list as $v) {
                $t_arr = $this->getSynonym($v);
                if ($t_arr) {
                    foreach ($t_arr as $t) {
                        if (!in_array($t, $t_pass_list)) {
                            if (in_array($t, $list)) {
                                $t_list[] = $v;
                                $t_pass_list = array_merge($t_pass_list, $t_arr);
                                break;
                            }
                        }
                    }
                } else {
                    $t_list[] = $v;
                }
            }
        }
        return $t_list;

    }







  
}
