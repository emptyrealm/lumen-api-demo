<?php
namespace ICSearch\libs;

use ICAnalysis\ICMfr\ICMfr;
use ICAnalysis\ICPack\ICPack;

class AnalysisKeyword
{

    public $keywordList = array();
    public $dictDelimiter = '|';

    public $keywordDictList = array();
    public $dictNumberPack = null;
    public $dictExtra = null;
    public $dictMaterial = null;
    public $dictPackNameKeywordList = array();
    public $dictCate = null;
    public $dictMfr = null;
    public $dictSpecUnit = null;
    public $dictSynonym = null;
    public $dictUnitSynonymList = array();
    public $dictSynonymList = array();
    public $dictNumberPackSynonym = null;
    public $numberRule = '((?:[0-9]\d*\.\d+)|(?:0\.\d*)|(?:[0-9]\d*))';
    public $dictPart = null;


    public $ICMfr=null;
    public $ICPack=null;

    function __construct()
    {
        $this->keywordDictList = $this->getKeywordDict();
        $this->dictNumberPack = Common::arrayGet($this->keywordDictList, 'numberPack');
        $this->dictExtra = Common::arrayGet($this->keywordDictList, 'extra');
        $this->dictMaterial = Common::arrayGet($this->keywordDictList, 'material');
        $this->dictPackNameKeywordList = Common::arrayGet($this->keywordDictList, 'packNameKeywordList');
        $this->dictCate = Common::arrayGet($this->keywordDictList, 'cateName');
        $this->dictSpecUnit = Common::arrayGet($this->keywordDictList, 'specUnit');
        $this->dictNumberPackSynonym = Common::arrayGet($this->keywordDictList, 'numberPackSynonym');
        $this->dictSynonym = Common::arrayGet($this->keywordDictList, 'cateSynonym') . $this->dictDelimiter
            . Common::arrayGet($this->keywordDictList, 'mfrSynonym') .
            $this->dictDelimiter . $this->dictNumberPackSynonym;
        $this->dictSynonymList = explode($this->dictDelimiter, $this->dictSynonym);
        $this->dictUnitSynonymList = !empty($this->keywordDictList['unitSynonym']) ? explode($this->dictDelimiter, $this->keywordDictList['unitSynonym']) : '';
        $this->dictPart = Common::arrayGet($this->keywordDictList, 'part');

        #将所有要用到的类先实例化
        $this->ICMfr=new ICMfr();
        $this->ICPack=new ICPack();
    }

    public function getKeywordDict()
    {
        return Common::loadDict('keyword_dict');
    }

    public function isMaterial($str)
    {
        return Common::isDictExist($this->dictMaterial, $str);
    }

    public function isShortPart($str)
    {
        return Common::isDictExist($this->dictPart, $str);
    }

    public function analysis($keyword, $searchObj)
    {
        $split_keyword_list = $this->splitKeyword($keyword);
        $split_keyword_list = array_filter($split_keyword_list);
        $keyword_list = array();
        //提取关键词类型
        $pass = [
            'LETTER',
            'CN_WORD',
            'ENGLISH',
            'ARABIC',
            'TYPE_CQUAN'
        ];
        $extra = [
            '白', '布', '橙', '粉', '钢', '镉', '铬', '公', '黑', '黄', '灰', '胶', '金', '蜡', '蓝', '铝', '绿', '母', '镍', '铁', '铜', '锡', '锌', '银', '紫', '棕', '红'
        ];
        foreach ($split_keyword_list as $keyword) {
            $spec_res = $this->getSpec($keyword);
            if ($spec_res === false) {
                $list = $searchObj->chineseWordSegmentation($keyword);
                foreach ($list as $item) {
                    if ((in_array($item['type'], $pass, true) && mb_strlen($item['token']) > 1) || in_array($item['token'], $extra)) {
                        $keyword_list[] = $item['token'];
                    }
                }
            } else if (is_array($spec_res)) {
                $keyword_list[] = $this->_formatSpecByArr($spec_res);
            }
        }
        if (empty($keyword_list)) {
            $keyword_list = $split_keyword_list;
        }
        $keyword_list = array_unique($keyword_list);
        return $keyword_list;
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

    public function splitKeyword($keyword, $delimiter = ' ')
    {
        return explode($delimiter, $keyword);
    }

    public function formatDesc($str)
    {
        if ($str) {
            $str = $this->unitConvert($str);
            $str = $this->formatSpec($str);
        }
        return $str;
    }

    public function _formatSpecByArr(array $arr, $len = 5, $numKey = 1, $unitKey = 4)
    {
        if (count($arr) == $len) {
            $num = $arr[$numKey];
            $unit = $arr[$unitKey];
            if (isset($arr[$numKey]) && isset($arr[$unitKey])) {
                return $this->_delZero($num) . $unit;
            }
        }
        return isset($arr[0]) ? $arr[0] : false;
    }

    /**
     * 删除数字小数点后的零
     * @param $num
     * @return float|string
     */
    function _delZero($num)
    {
        $num = trim($num);
        if (preg_match("#\.{1}#", $num)) {
            $num = (float)$num;
        }
        return $num;
    }

    /**
     * @param $str
     * @param null $unit
     * @return bool
     */
    public function isSpec($str, $unit = null)
    {
        $str=$this->formatSpec($str);
        return $this->getSpec($str, $unit) !== false ? true : false;
    }

    /**
     * @param $str
     * @param $unit
     */
    public function getSpec($str, $unit = null)
    {
        $unit = !empty($unit) ? $unit : $this->dictSpecUnit;
        if ($str && $unit) {
            $unit_str = '(' . $unit . ')';
            if (preg_match("#^{$this->numberRule}{$unit_str}{1}$#is", $str, $matches)) {
                return $matches;
            } else {
                return false;
            }
        }
        return false;
    }

    public function numberRuleTest()
    {
        $list = [
            0.02,
            0,
            3000,
            2.00,
            2.0,
            0.0007,
            1.20258,
            10,
            2000
        ];
        foreach ($list as $v) {
            if (preg_match("#^{$this->numberRule}{1}$#is", $v, $matches)) {
                print_r($matches);
            } else {
                Common::dd($v);
            }
        }
        exit;

    }


    /**
     * 格式化参数
     * @param $keyword
     * @return string
     */

    public function formatSpec($keyword)
    {
        $keyword = ' ' . $keyword . ' ';
        $num_rule = '(?:[ ,/\\-\\+]{1})' . '((?:[0-9]\d*\.\d+)|(?:0\.\d*)|(?:[0-9]\d*))';
        $unit_str = '(' . $this->dictSpecUnit . ')';

        $keyword = preg_replace_callback("#{$num_rule}[ ]*{$unit_str}{1}(?:[ ,]{1})#i", function ($s) {
            return ' ' . $this->_formatSpecByArr($s, 3, 1, 2) . ' ';
        }, $keyword);
//        ven_debug($count);
        $keyword = preg_replace_callback("#[ ]([0-9]+)-(pin|bit|port|in|element|pole){1}[ ]#i", function ($s) {
            return ' ' . $this->_formatSpecByArr($s, 3, 1, 2) . ' ';
        }, $keyword);
        return trim($keyword);
    }


    public function formatKeyword($str)
    {
        if ($str) {
            $replace_list = [
                "±", "/", ",",
                "，", "。",
                "_", "-",
            ];
            foreach ($replace_list as $value) {
                $str = str_replace($value, ' ', $str);
            }
            $str = $this->formatSpec($str);
        }
        return trim(strtolower($str));
    }

    public function isCate($keyword)
    {
        return !empty(Common::dictExist($this->dictCate, $keyword) !== false) ? true : false;
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

    public function getDataByLowerKey(array $arr, $v)
    {
        if ($v != '' && !is_null($v) && isset($arr[strtolower($v)])) {
            return $arr[strtolower($v)];
        } else {
            return $v;
        }
    }

    public function isEqualByList($str, $list)
    {
        if ($str) {
            foreach ($list as $v) {
                if (preg_match($v, $str)) {
                    return true;
                    break;
                }
            }
        }
        return false;
    }

    /**
     *
     * @param [type] $str
     * @return void
     */
    public function englishIncludeRegular($str)
    {
        return "#([ -]{$str}[ -])|(^{$str}[ -])|([ -]{$str}$)|(^{$str}$)#i";
    }

    public function chineseIncludeRegular($str)
    {
        return "#(.*){$str}(.*)#i";
    }



    /**
     * @param $keyword
     * @return null|string|string[]
     */
    public function removeExtraSpace($keyword)
    {
        $str = null;
        if ($keyword) {
            $str = preg_replace("/\s(?=\s)/i", "\\1", $keyword);
        }
        return $str;
    }


    /**
     * TODO 使用新的类
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



    #TODO 使用新的类
    public function synonymStr($str)
    {
        $str = $this->unitConvert($str);
        $str = $this->replaceStr($str);
        return trim(strtolower($str));
    }


    

}
