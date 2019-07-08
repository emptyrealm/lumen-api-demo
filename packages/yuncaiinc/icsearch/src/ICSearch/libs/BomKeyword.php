<?php
namespace ICSearch\libs;

use ICAnalysis\ICPack\ICPack;
use ICAnalysis\ICMfr\ICMfr;

/**
 * 处理bom的关键词
 * Class BomKeyword
 */
class BomKeyword
{

    public $bomDictList = array();
    public $dictEnglishInvalid = null;
    public $dictChineseInvalid = null;

    private $keywordPortrait;
    public $analysisKeyword;

    public function __construct($analysisKeyword,$keywordPortrait)
    {
        $this->keywordPortrait=$keywordPortrait;
        $this->analysisKeyword=$analysisKeyword;
        $this->bomDictList = $this->getBomDict();
        $this->dictEnglishInvalid = Common::arrayGet($this->bomDictList, 'englishInvalid');
        $this->dictChineseInvalid = Common::arrayGet($this->bomDictList, 'chineseInvalid');
        $this->analysisKeyword->dictCate = Common::arrayGet($this->bomDictList, 'cateName');        
    }

    /**
     * 获取词典
     * @return mixed
     */
    public function getBomDict()
    {
        return Common::loadDict('bom_dict');
    }


    /**
     * @param $list
     * @return array
     */
    public function splitKeywordByList($list)
    {
        $list=$this->splitKeywordBySymbol($list);
        $list=$this->splitKeywordByPack($list);
        return $list;
    }

    public  function splitKeywordByPack($list){
        $res=[];
        if (is_array($list)){
            foreach ($list as $key=>$v){
                if (preg_match("#^({$this->analysisKeyword->dictNumberPack})(f)$#i", $v, $arr)) {
                    if (count($res)==3){
                        $res[]=$arr[1];
                        $res[]=$arr[2];
                    }
                }else{
                    $res[]=$v;
                }
            }
        }
        return $res;
    }

    public function splitKeywordBySymbol($list,$symbol='-'){
        $keywordList = array();
        if ($list) {
            $keywordList = array();
            $ICPack= new ICPack();
            foreach ($list as $v) {
                if ($v != '') {
                    $pass = false;
                    if (!$ICPack->isPack($v)) {
                        $tList = $this->analysisKeyword->splitKeyword($v, $symbol);
                        if (!empty($tList) && count($tList) > 1) {
                            foreach ($tList as $t) {
                                if ($this->analysisKeyword->isSpec($t)) {
                                    $pass = true;
                                    break;
                                } elseif ($ICPack->isPack($t)) {
                                    $pass = true;
                                    break;
                                } elseif (Common::isDictExist($this->analysisKeyword->dictExtra, $t)) {
                                    $pass = true;
                                    break;
                                } elseif ($this->analysisKeyword->isMaterial($t)) {
                                    $pass = true;
                                    break;
                                }
                            }
                        }
                    }
                    if ($pass && !empty($tList)) {
                        $keywordList = array_merge($keywordList, $tList);
                    } else {
                        $keywordList[] = $v;
                    }
                }
            }
        }
        return $keywordList;
    }

    /**
     * 删除无效中文字符
     * @param $keywordList
     * @return mixed
     */
    public function removeInvalidChinese($keywordList)
    {
        foreach ($keywordList as $key => $value) {
            if (Common::isDictExist($this->dictChineseInvalid, trim($value))) {
                unset($keywordList[$key]);
            }
        }
        return $keywordList;
    }

    /**
     * 删除无效英文
     * @param $keywordList
     * @return mixed
     */
    public function removeInvalidEnglish($keywordList)
    {
        foreach ($keywordList as $key => $value) {
            if (Common::isDictExist($this->dictEnglishInvalid, trim($value))) {
                unset($keywordList[$key]);
            }
        }
        return $keywordList;
    }

//    /**
//     * 分割关键词通过列表
//     * @param $list
//     * @return array
//     */
//    public function splitKeywordByList($list)
//    {
//        $keywordList = array();
//        //词提取
//        foreach ($list as $keyword) {
//            //提取括号内容
//            if (preg_match("/((.*)+)\((.*)\)/i", $keyword, $arr)) {
//                if (count($arr) == 4) {
//                    $symbol = null;
//                    $keywordList[] = $arr[1];
//                    $str = $arr[3];
//                    if (stripos($str, ',') !== false) {
//                        $symbol = ',';
//                    }
//                    if (!$symbol && stripos($str, ' ') !== false) {
//                        $symbol = ' ';
//                    }
//                    if ($symbol) {
//                        $tList = explode($symbol, $str);
//                        foreach ($tList as $t) {
//                            $keywordList[] = $t;
//                        }
//                    } else {
//                        $keywordList[] = $str;
//                    }
//                }
//            } else {
//                if (count(explode(',', $keyword)) > 1) {
//                    $keywordList = array_merge($keywordList, explode(',', $keyword));
//                } else {
//                    $tKeyList = explode(' ', $keyword);
//                    $ttKeyList = array();
//                    $tttKeyList = array();
//                    if (count($tKeyList) > 1) {
//                        foreach ($tKeyList as $k => $v) {
//                            if ($this->analysisKeyword->isSpec($v) || in_array(strtolower($v), ['j', 'k', 'f'])) {
//                                $ttKeyList[] = $v;
//                            } elseif ($ICPack->isPack($v)) {
//                                $ttKeyList[] = $v;
//                            } elseif (Common::isDictExist($this->analysisKeyword->dictExtra, $v)) {
//                                $ttKeyList[] = $v;
//                            } elseif ($this->analysisKeyword->isMaterial($v)) {
//                                $ttKeyList[] = $v;
//                            }
//                        }
//                        if (count($tKeyList) > count($ttKeyList)) {
//                            $tttKeyList = array_diff($tKeyList, $ttKeyList);
//                        }
//                        //剩下的还原回去
//                        if (!empty($tttKeyList)) {
//                            $keywordList[] = implode(" ", $tttKeyList);
//                        }
//                        if ($ttKeyList) {
//                            $keywordList = array_merge($keywordList, $ttKeyList);
//                        }
//                    } else {
//                        $keywordList[] = $keyword;
//                    }
//                }
//            }
//        }
//        return $keywordList;
//    }


    /**
     * 删除前后字符
     * @param $str
     * @return string
     */
    public function trimSymbol($str)
    {
        return trim($str, '{}[]()/ -_\+');
    }

    /**
     * 获取分割符
     * @param $str
     * @return string
     */
    public function getDelimiter($str)
    {
        $delimiter = ' ';
        $defaultStrCount = mb_substr_count($str, ' ');
        $arr = [
            [
                'delimiter' => ',',
                'len' => mb_substr_count($str, ','),
            ],
            [
                'delimiter' => '_',
                'len' => mb_substr_count($str, '_'),
            ],
            [
                'delimiter' => '/',
                'len' => mb_substr_count($str, '/'),
            ],
            [
                'delimiter' => '|',
                'len' => mb_substr_count($str, '|'),
            ],
        ];
        $newArr = u_array_sort($arr, 'len', SORT_DESC);
        $firstArr = array_get($newArr, 0, array());
        if (!empty($firstArr['len']) && $firstArr['len'] >= 2 && $firstArr['len'] >= $defaultStrCount) {
            $delimiter = $firstArr['delimiter'];
        } elseif (!$defaultStrCount && mb_substr_count($str, '-') >= 2) {
            $delimiter = '-';
        }
        return $delimiter;
    }

    /**
     * 通过关键词加工关键词组
     * @param $keyword
     * @return mixed|null|string|string[]
     */
    public function processBomKeywordByKeyword($keyword)
    {
        $keyword = $this->analysisKeyword->removeExtraSpace($keyword);//去多个空格
        $keyword = $this->removeDate($keyword);//删除日期
        $keyword = $this->analysisKeyword->unitConvert($keyword);//转换特殊写法
        $keyword = $this->analysisKeyword->replaceStr($keyword);   //替换特殊写法字符
        $keyword = $this->analysisKeyword->removeExtraSpace($keyword); //去多个空格
        $keyword = $this->_formatSpec($keyword); //格式化参数
        $keyword = $this->replaceVolume($keyword); //体积写法格式化
        $keyword = $this->replaceItemValue($keyword); //项值提取
        $keyword = $this->analysisKeyword->removeExtraSpace($keyword);//去多个空格
        return $keyword;
    }


    /**
     * 参数格式化
     * @param $list
     * @return array
     */
    public function formatSpecList($list){
        if ($list && is_array($list))
        {
            foreach ($list as $k=>$v){
                $v = trim($v,"+-");
                $list[$k] = $this->_formatSpec($v);
            }
        }
        return $list;
    }


    /**
     * #TODO 7.0 ohms 可以被处理成7ohms,但7.0ohms，处理不成功
     * 格式化参数
     * @param $keyword
     * @return string
     */

    public function _formatSpec($keyword)
    {
        $keyword = $this->analysisKeyword->formatSpec($keyword);
        $keyword = ' ' . $keyword . ' ';
        //处理数量+k 单位的写法 100k ohm
        $keyword = preg_replace_callback("#[ ]([0-9.]+k) (ohm|ohms){1}[ ]#i", function ($s) {
            return ' ' . $this->analysisKeyword->_formatSpecByArr($s, 3, 1, 2) . ' ';
        }, $keyword);
        //处理数量+k 单位的写法 1K2 OHM
        $keyword = preg_replace_callback("#[ ]([0-9]+)k([0-9]{0,2}) (ohm|ohms){1}[ ]#i", function ($s) {
            if (count($s) == 4) {
                $num = $s[1] . '.' . $s[2];
                $unit = $s[3];
                if ($num && $unit) {
                    return ' ' . $this->analysisKeyword->_delZero($num) . 'k' . $unit . ' ';
                }
            }
            return isset($s[0]) ? $s[0] : false;
        }, $keyword);
        //  ven_debug($keyword);
        return trim($keyword);
    }


    public function removeDate($str)
    {
        $date = "/([0-9]{3}[1-9]|[0-9]{2}[1-9][0-9]{1}|[0-9]{1}[1-9][0-9]{2}|[1-9][0-9]{3})-(((0[13578]|1[02])-(0[1-9]|[12][0-9]|3[01]))|((0[469]|11)-(0[1-9]|[12][0-9]|30))|(02-(0[1-9]|[1][0-9]|2[0-8])))/is";
        return preg_replace($date, " ", $str);
    }

    /**
     * 加工关键词组
     * @param $keyword
     * @return array|string
     */
    public function keywordListByKeyword($keyword)
    {
        $delimiter = $this->getDelimiter($keyword);
        $keywordList = $this->analysisKeyword->splitKeyword($keyword, $delimiter);
        return $keywordList;
    }

    /**
     * 加工关键词组
     * @param $keywordList
     * @return array|string
     */
    public function processBomKeywordList($keywordList)
    {
        $keywordList = $this->replaceCommonStrByList($keywordList);//整词替换
        $keywordList = $this->splitKeywordByList($keywordList);//再次细分关键词
        $keywordList = $this->formatPacks($keywordList);//格式化封装
        $keywordList = $this->formatSpecList($keywordList);
        return $keywordList;
    }

    public function formatPacks($list)
    {
        $ICPack=new ICPack();
        foreach ($list as $key => $value) {
            $list[$key]=$ICPack->formatPack($value);
        }
        return $list;
    }


    public function removeInvalidUnit($keywordList)
    {
        if ($keywordList) {
            foreach ($keywordList as $k => $v) {
                if (Common::isDictExist($this->analysisKeyword->dictSpecUnit, $v)) {
                    unset($keywordList[$k]);
                }
            }
        }
        return array_values($keywordList);
    }

    public function removeDesign($keywordList){
        if ($keywordList) {
            foreach ($keywordList as $k => $v) {
                if(preg_match("#^[A-Za-z]{1}([0-9]{1,3})$#",$v)){
                    //单字母+数字长度小于等于3
                    unset($keywordList[$k]);
                }
            }
        }
        return array_values($keywordList);
    }

    public function replaceCommonStrByList($keywordList)
    {
        if ($keywordList) {
            $list = [
                [
                    'str' => 'o402',
                    'replace' => '0402'
                ],
                [
                    'str' => 'o603',
                    'replace' => '0603'
                ],
                [
                    'str' => 'o805',
                    'replace' => '0805'
                ],
                [
                    'str' => 'cog',
                    'replace' => 'C0G'
                ],
                [
                    'str' => 'npo',
                    'replace' => 'np0'
                ],
            ];
            $keywordList=$this->replaceStrByList($keywordList,$list);
        }
        return $keywordList;

    }

    public function replaceStrByList($keywordList,$rules){
        if ($keywordList){
            foreach ($keywordList as $k => $value) {
                foreach ($rules as $item) {
                    if ($item['str'] == strtolower($value)) {
                        $keywordList[$k] = $item['replace'];
                    }
                }
            }
        }
        return $keywordList;

    }

    /**
     * 通过关键词获取分类
     * @param $keyword
     * @return mixed|null
     */
    public function getCateByKeyword(&$keyword)
    {
        $cate_name = null;
        $cate_list = explode($this->analysisKeyword->dictDelimiter, $this->analysisKeyword->dictCate);
        foreach ($cate_list as $v) {
            $t_keyword = str_ireplace(["_", ",", "/", "|"], ' ', $keyword);
            $t_keyword = ' ' . $t_keyword . ' ';
            if ($t_keyword) {
                if (stripos($t_keyword, ' ' . $v . ' ') !== false) {
                    $cate_name = $v;
                    $keyword = str_ireplace($v, " ", $keyword);
                    break;
                }
            }
        }
        return $cate_name;
    }

    /**
     * 提取分类
     * @param $cateKeywordList
     * @param $keywordList
     * @return array
     */

    public function getCateList($cateKeywordList, $keywordList)
    {
        if ($keywordList && is_array($keywordList)) {
            foreach ($keywordList as $key => $value) {
                if ($this->analysisKeyword->isCate($value)) {
                    if ($value) {
                        $cateKeywordList[] = $value;
                    }
                }
            }
        }
        if (empty($cateKeywordList)) {
            if ($this->keywordPortrait->isResistor()) {
                $cateKeywordList[] = '电阻';
            } else if ($this->keywordPortrait->isCapacitor()) {
                $cateKeywordList[] = '电容';
            } else if ($this->keywordPortrait->isInductance()) {
                $cateKeywordList[] = '电感';
            } else if ($this->keywordPortrait->isCrystalOscillator()) {
                $cateKeywordList[] = '晶振';
            }else if ($this->keywordPortrait->isConnector()) {
                $cateKeywordList[] = '连接器';
            }
        }
        return Common::icArrayIunique($cateKeywordList);
    }


    /**
     * 通过分类修改关键词组
     * @param $keywordList
     * @return array
     */

    public function setKeywordListByCateList($keywordList)
    {
        $numberPackList = [];
        if ($this->keywordPortrait->isResistor()) {
            foreach ($keywordList as $k => $value) {
                if (preg_match("#^([0-9]+)([KR]){1}([0-9]+)$#", $value, $arr)) {
                    if (count($arr) == 4) {
                        $t = strtoupper($arr[2]);
                        switch ($t) {
                            case 'R':
                                $keywordList[$k] = $arr[1] . '.' . $arr[3] . 'ohms';
                                break;
                            case 'K':
                                $keywordList[$k] = $arr[1] . '.' . $arr[3] . $arr[2] . 'ohms';
                        }
                    }
                }
            }
            foreach ($keywordList as $key => $value) {
                if (preg_match("#(^[0-9.]+)(k|m|perfs|k0)$#i", $value, $arr)) {
                    if (count($arr) == 3) {
                        switch (strtolower($arr[2])) {
                            case 'k':
                            case 'k0':
                                $keywordList[$key] = $arr[1] . 'kohms';
                                break;
                            case 'm':
                                $keywordList[$key] = $arr[1] . 'mohms';
                                break;
                            case 'perfs':
                                $keywordList[$key] = $arr[1] . 'per';
                                break;
                        }
                    }
                } else {
                    $keywordList[$key] = $this->analysisKeyword->getDataByLowerKey(["f" => '1per', "j" => '5per'], $value);
                }
                $tNumberPackList = $this->getNumberPackByKeyword($value);
                $numberPackList = array_merge($numberPackList, $tNumberPackList);
            }
        }
        if ($this->keywordPortrait->isCapacitor()) {
            foreach ($keywordList as $key => $value) {
                if (preg_match("#(^[0-9.]+)(p|u|n)$#i", $value, $arr)) {
                    if (count($arr) == 3) {
                        switch (strtolower($arr[2])) {
                            case 'p':
                                $keywordList[$key] = $arr[1] . 'pf';
                                break;
                            case 'u':
                                $keywordList[$key] = $arr[1] . 'uf';
                                break;
                            case 'n':
                                $keywordList[$key] = $arr[1] . 'nf';
                                break;
                        }
                    }
                } else {
                    $keywordList[$key] = $this->analysisKeyword->getDataByLowerKey(["j" => '5per', 'k' => "10per"], $value);
                }
                $tNumberPackList = $this->getNumberPackByKeyword($value);
                $numberPackList = array_merge($numberPackList, $tNumberPackList);
            }
        }
        if ($this->keywordPortrait->isMos()){
            $rules =[
                [
                    'str' => 'n',
                    'replace' => 'n-channel'
                ],
                [
                    'str' => 'n-ch',
                    'replace' => 'n-channel'
                ],
                [
                    'str' => 'N-沟道',
                    'replace' => 'n-channel'
                ],
                [
                    'str' => 'p',
                    'replace' => 'p-channel'
                ],
                [
                    'str' => 'p-ch',
                    'replace' => 'p-channel'
                ],
                [
                    'str' => 'p-沟道',
                    'replace' => 'p-channel'
                ],
                [
                    'str' => '沟道',
                    'replace' => 'channel'
                ],
            ];
            $keywordList=$this->replaceStrByList($keywordList,$rules);
        }

        if ($numberPackList) {
            $keywordList = array_merge($keywordList, $numberPackList);
        }
        return Common::icArrayIunique($keywordList);

    }

    /**
     * 通过关键字提取字符串
     * @param $str
     * @return array
     */
    public function getNumberPackByKeyword($str)
    {
        $res = [];
        //判断纯数字则不处理
        if (preg_match("#[^0-9]+#", $str)) {
            if (preg_match_all("#{$this->analysisKeyword->dictNumberPack}+#", $str, $t_list)) {
                if (!empty($t_list[0])) {
                    $res = $t_list[0];
                }
            }
        }
        return $res;

    }

    /**
     * 提取制造商组
     * @param $keywordList
     * @return array
     */
    public function getMfrList( &$keywordList)
    {
        $mfrKeywordList=array();
        if ($keywordList && is_array($keywordList)) {
            foreach ($keywordList as $key => $value) {
                if ($this->analysisKeyword->ICMfr->isMfr($value)) {
                    if ($value) {
                        $mfrKeywordList[] = $value;
                    }
                    if (isset($keywordList[$key])) {
                        unset($keywordList[$key]);
                    }
                }
            }
        }
        return Common::icArrayIunique($mfrKeywordList);
    }


    /**
     * 对关键词组进行过滤
     * @param $keywordList
     * @return array
     */
    public function filterBomKeywordByList($keywordList)
    {
        $extraWord = [
            '白', '布', '橙', '粉', '钢', '镉', '铬', '公', '黑', '黄', '灰', '胶', '金', '蜡', '蓝', '铝', '绿', '母', '镍', '铁', '铜', '锡', '锌', '银', '紫', '棕', '红'
        ];
        $analysisKeywordList = array();
        $keywordList = $this->removeInvalidChinese($keywordList);
        $tAnalysisKeywordList = $this->removeInvalidEnglish($keywordList);
        foreach ($tAnalysisKeywordList as $key => $k) {
            $t_keyword = $this->trimSymbol($k);
            if (mb_strlen($t_keyword) > 1 || in_array($k, $extraWord)) {
                $analysisKeywordList[$key] = $t_keyword;
            }
        }
        $analysisKeywordList = array_filter(Common::icArrayIunique($analysisKeywordList));
        return $analysisKeywordList;
    }

    public function getPartQueryKeywordList($keywordList)
    {
        $res = [];
        $original_keyword_list = $this->replaceCommonStrByList($keywordList);
        if ($original_keyword_list) {
            foreach ($original_keyword_list as $value) {
                if ($value) {
                    $list = $this->_getPartQueryKeywordList($value);
                    $res = array_merge($res, $list);
                    $res [] = $value;
                }
            }
        }
        $ICPack=new ICPack();
        //filter
        if ($res){
            foreach ($res as $key=>$v){
                $filter=false;
                if (preg_match_all("#([0-9.])+\*([0-9.])+\*([0-9.])+#", $v)) {
                    $filter=true;
                }elseif (strtoupper($v)=='N/A'){
                    $filter=true;
                }elseif($ICPack->isPack($v)){
                    $filter=true;
                }elseif($this->analysisKeyword->isSpec($v)){
                    $filter=true;  
                }elseif($this->analysisKeyword->ICMfr->IsMfr($v)){
                    $filter=true;  
                }elseif($this->analysisKeyword->isCate($v)){
                    $filter=true;  
                }
                if ($filter){
                    unset($res[$key]);
                }
            }
        }
        $res = array_filter(Common::icArrayIunique($res));
        return $res;
    }

    public function _getPartQueryKeywordList($keywordStr,$isMerge=false)
    {
        $keywordStr = $this->processBomKeywordByKeyword($keywordStr);
        //取第一个关键词组
        $keywordList = preg_split("/[,| \\(\\):]+/", $keywordStr);
        $analysisKeywordList = array();
        if ($isMerge){
            $tKeywordList = [];
            $newKeywordList = array();
            if ($keywordList) {
                //标记每个词组的合并状态
                foreach ($keywordList as $value) {
                    $is_merge = true;
                    if (
                        Common::hasChinese($value)
                        || $this->analysisKeyword->isSpec($value)
                        || $this->analysisKeyword->isCate($value)
                        || $this->analysisKeyword->ICMfr->isMfr($value)
                    ) {
                        $is_merge = false;
                    }
                    $tKeywordList[] = [
                        'str' => $value,
                        'merge' => $is_merge
                    ];
                }
                $str_arr_count = count($tKeywordList);
                //混合字符串
                if ($str_arr_count >= 1) {
                    $j = 0;
                    while (true) {
                        $k = $str_arr_count - $j;
                        while (true) {
                            if ($k == 0) {
                                break;
                            }
                            $arr = array_slice($tKeywordList, $j, $k);
                            $t_str = '';
                            if ($arr) {
                                foreach ($arr as $item) {
                                    if (!$item['merge']) {
                                        break;
                                    }
                                    $t_str .= ' ' . $item['str'];
                                }
                            }
                            if ($t_str) {
                                $newKeywordList[] = $t_str;
                            }
                            $k--;
                        }
                        $j++;
                        if ($j == $str_arr_count) {
                            break;
                        }
                    }
                }
            }
            $newKeywordList = Common::icArrayIunique($newKeywordList);
        }else{
            $newKeywordList = Common::icArrayIunique($keywordList);
        }

        $ICPack=new ICPack();
        //过滤组合字符串
        foreach ($newKeywordList as $key => $v) {
            $v = $this->trimSymbol($v);
            $v_v_len = mb_strlen(preg_replace("#[^A-Za-z0-9]#", "", $v));
            
            if ($v_v_len >= 2 && $v_v_len <= 30) {
                $is_pass = true;
                if ($this->analysisKeyword->isSpec($v)
                    || $ICPack->isNumberPack($v)
                    || $this->analysisKeyword->isMaterial($v)
                    || preg_match("#^[A-Za-z]{1,3}(" . $this->analysisKeyword->dictNumberPack . ")$#", $v)
                    || $this->analysisKeyword->isCate($v)
                ) {
                    $is_pass = false;
                }
                if ($is_pass) {
                    $analysisKeywordList[] = $v;
                }
            }
        }
        $analysisKeywordList = array_filter(Common::icArrayIunique($analysisKeywordList));
        return $analysisKeywordList;
    }


    /**
     * 替换特殊字符
     * @param $keyword
     * @return mixed
     */
    public function replaceItemValue($keyword)
    {
        $pattern = [
            "#([A-Za-z0-9\x7f-\xff]+):([A-Za-z0-9\x7f-\xff]+)#",
            "#([A-Za-z0-9\x7f-\xff]+)=([A-Za-z0-9\x7f-\xff]+)#",
        ];
        $replacement = [
            '$2',
            '$2'
        ];
        $keyword = preg_replace($pattern, $replacement, $keyword);
        return $keyword;
    }

    /**
     *
     * @param $keyword
     * @return mixed
     */
    public function replaceVolume($keyword)
    {
        $pattern = [
            "#([0-9\.]+)×([0-9\.]+)×([0-9\.]+)[ ](mm)#",
        ];
        $replacement = [
            '$1$4*$2$4*$3$4'
        ];
        $keyword = preg_replace($pattern, $replacement, $keyword);
        return $keyword;
    }




}
