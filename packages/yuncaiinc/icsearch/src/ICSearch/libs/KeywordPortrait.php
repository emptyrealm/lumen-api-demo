<?php

namespace ICSearch\libs;

/**
 * 关键词类
 */

class KeywordPortrait
{

    public $keywordList = null;
    public $originalList = null;
    public $cateCode = null;
    private $analysisKeyword;


    const CATE_RESISTOR = 'resistor';//电阻
    const CATE_CAPACITOR = 'capacitor';//电容
    const CATE_INDUCTANCE='inductance';// 电感
    const CATE_CRYSTAL_OSCILLATOR='crystal_oscillator';//晶振
    const CATE_CONNECTOR='connector';//连接器
    const CATE_MOS='mos';// 电感

    #TODO 传对象还是直接new?
    public function __construct($analysisKeyword)
    {
        $this->analysisKeyword=$analysisKeyword;
    }

    public function setKeyword($key, $value)
    {
        $this->keywordList[$key] = $value;
    }

    public function getKeywordList()
    {
        return $this->keywordList;
    }

    public function getKeyword($key,$default=array())
    {
        return array_get($this->keywordList, $key, $default);
    }

    public function setOriginal($key, $value)
    {
        $this->originalList[$key] = $value;
    }


    //电阻
    public function isResistor()
    {
        return $this->cateCode == self::CATE_RESISTOR ? 1 : 0;
    }

    //电容
    public function isCapacitor()
    {
        return $this->cateCode == self::CATE_CAPACITOR ? 1 : 0;
    }

    //电感
    public function isInductance()
    {
        return $this->cateCode == self::CATE_INDUCTANCE ? 1 : 0;
    }

    //晶振
    public function isCrystalOscillator()
    {
        return $this->cateCode == self::CATE_CRYSTAL_OSCILLATOR ? 1 : 0;
    }

    //连接器
    public function isConnector()
    {
        return $this->cateCode == self::CATE_CONNECTOR ? 1 : 0;
    }

    //MOS管
    public function isMos()
    {
        return $this->cateCode == self::CATE_MOS ? 1 : 0;
    }

    //电阻
    public function setCateResistor(array $keywordList,$design)
    {
        if (empty($this->cateCode)) {
            if (!empty($keywordList)) {
                foreach ($keywordList as $v) {
                    $packList=[
                        'R0402',
                        'R0603',
                        'R0805'
                    ];
                    if($v){
                        if($this->analysisKeyword->isEqualByList($v, [
                            $this->analysisKeyword->englishIncludeRegular('res'),
                            $this->analysisKeyword->chineseIncludeRegular("电阻"),
                            $this->analysisKeyword->englishIncludeRegular('resistor'),
                            $this->analysisKeyword->englishIncludeRegular('resistors')
                        ])
                        || in_array(strtoupper($v),$packList)
                        ){
                            $this->cateCode = self::CATE_RESISTOR;
                            break;
                        }

                    }
                }
            }
            if (!empty($design) && is_array($design)) {
                foreach ($design as $value) {
                    if ($value && $this->isDesign("R",$value)) {
                        $this->cateCode = self::CATE_RESISTOR;
                        break;
                    }
                }
            }

        }

    }

    //电容
    public function setCapacitor(array $keywordList,$design)
    {
        if (empty($this->cateCode)) {
            if ($keywordList) {
                foreach ($keywordList as $v) {
                    if($v){
                        $packList=[
                            'C0402',
                            'C0603',
                            'C0805'
                        ];
                        if($this->analysisKeyword->isEqualByList($v, [
                            $this->analysisKeyword->englishIncludeRegular('cap'),
                            $this->analysisKeyword->chineseIncludeRegular("电容"),
                            $this->analysisKeyword->englishIncludeRegular('capacitor'),
                            $this->analysisKeyword->englishIncludeRegular('capacitors')
                        ])
                        || in_array(strtoupper($v),$packList)
                        ){
                            $this->cateCode = self::CATE_CAPACITOR;
                            break;
                        }
                    }
                }
            }
            if (!empty($design) && is_array($design)) {
                foreach ($design as $value) {
                    if ($value && $this->isDesign("C",$value)) {
                        $this->cateCode = self::CATE_CAPACITOR;
                        break;
                    }
                }
            }

        }

    }

    //电感
    public function setInductance(array $keywordList)
    {
        if (empty($this->cateCode)) {
            if ($keywordList) {
                foreach ($keywordList as $v) {
                    if ($v && $this->analysisKeyword->isEqualByList($v, [
                            $this->analysisKeyword->englishIncludeRegular('inductor'),
                            $this->analysisKeyword->chineseIncludeRegular("电感"),
                            $this->analysisKeyword->englishIncludeRegular('inductors'),
                            $this->analysisKeyword->englishIncludeRegular('ind')
                        ])) {
                        $this->cateCode = self::CATE_INDUCTANCE;
                        break;
                    }
                }
            }
        }
    }

    //晶振
    public function setCrystalOscillator(array $keywordList)
    {
        if (empty($this->cateCode)) {
            if ($keywordList) {
                foreach ($keywordList as $v) {
                    if ($v && $this->analysisKeyword->isEqualByList($v, [
                            $this->analysisKeyword->englishIncludeRegular('crystal oscillator'),
                            $this->analysisKeyword->chineseIncludeRegular("晶振"),
                            $this->analysisKeyword->chineseIncludeRegular("晶体振荡器"),
                            $this->analysisKeyword->englishIncludeRegular('crystal'),
                            $this->analysisKeyword->englishIncludeRegular('xtal'),
                            $this->analysisKeyword->englishIncludeRegular('xtals')
                        ])) {
                        $this->cateCode = self::CATE_CRYSTAL_OSCILLATOR;
                        break;
                    }
                }
            }
        }
    }

    //连接器
    public function setConnector(array $keywordList)
    {
        if (empty($this->cateCode)) {
            if ($keywordList) {
                foreach ($keywordList as $v) {
                    if ($v && $this->analysisKeyword->isEqualByList($v, [
                            $this->analysisKeyword->englishIncludeRegular('connector'),
                            $this->analysisKeyword->englishIncludeRegular('connectors'),
                            $this->analysisKeyword->chineseIncludeRegular("连接器"),
                            $this->analysisKeyword->chineseIncludeRegular("座子"),
                            $this->analysisKeyword->chineseIncludeRegular("母座"),
                            $this->analysisKeyword->chineseIncludeRegular("地座"),
                            $this->analysisKeyword->chineseIncludeRegular("端子"),
                            $this->analysisKeyword->chineseIncludeRegular("直针"),
                            $this->analysisKeyword->englishIncludeRegular('conn'),
                        ])) {
                        $this->cateCode = self::CATE_CONNECTOR;
                        break;
                    }
                }
            }
        }
    }

    //mos管
    public function setMos(array $keywordList)
    {
        if (empty($this->cateCode)) {
            if ($keywordList) {
                foreach ($keywordList as $v) {
                    if ($v && $this->analysisKeyword->isEqualByList($v, [
                            $this->analysisKeyword->englishIncludeRegular('mosfet'),
                            $this->analysisKeyword->chineseIncludeRegular("mos管"),
                            $this->analysisKeyword->englishIncludeRegular('mos'),
                        ])) {
                        $this->cateCode = self::CATE_MOS;
                        break;
                    }
                }
            }
        }
    }

    public function isDesign($letter,$str){
        return preg_match("#^{$letter}([0-9])+$#", $str);
    }

    public function _setDesign($design)
    {
        $this->setOriginal('design', $design);

        if ($design) {
            $designList = [];
            $list = preg_split("#[, /]#", $design);
            foreach ($list as $value) {
                if (trim($value)) {
                    $designList[] = trim($value);
                }
            }
            if ($designList) {
                $this->setKeyword('design', $designList);
            }
        }
    }

    public function getKeywordSynonym(array $list)
    {
        $t=[];
        if ($list){
            foreach ($list as $v){
                $tSynonymList = $this->analysisKeyword->getSynonym($v);
                $t[$v]=[
                    'str' => $v,
                    'synonym' => $tSynonymList
                ];
            }
        }
        return $t;
    }

    public function getHitKeywordList($highlight)
    {
        $res = [];
        $highlightList = $this->_getKeywordByHighlight($highlight);
        if ($highlightList) {
            foreach ($highlightList as $k => $v) {
                $highlightList[$k] = $this->analysisKeyword->synonymStr($v);
            }
        }

        foreach ($this->getKeyword('synonym') as $item) {
            $t_list = $item['synonym'];
            $t_list[] = $item['str'];
            if (array_intersect(array_map("StrToLower", $t_list), array_map("StrToLower", $highlightList))) {
                $res[] = $item['str'];
            }
        }
        return $res;
    }

    public function _getKeywordByHighlight($list)
    {
        $res = [];
        if ($list && is_array($list)) {
            foreach ($list as $k => $item) {
                $t_list = array();
                if ($item && is_array($item)) {
                    foreach ($item as $v) {
                        $t_list = $this->getHighlightKeyword($v);
                    }
                    $res = array_merge($res, $t_list);
                }
            }
        }
        return Common::icArrayIunique($res);
    }

    public function getHighlightKeyword($keyword)
    {
        $res = [];
        if ($keyword) {
            if (preg_match_all("#<em class=\"i_key\">" . '(.*?)' . "</em>#", $keyword, $arr)) {
                $res = Common::arrayGet($arr, 1, array());
            }
        }
        return $res;

    }


    public function getCateCode(){
        return $this->cateCode;
    }


    
}
