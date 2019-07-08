<?php

namespace ICSearch;


use ICSearch\libs\AnalysisKeyword;
use ICSearch\libs\Common;
use ICSearch\libs\CustomConfig;
use ICSearch\libs\ESQuery;
use ICAnalysis\ICPack\ICPack;

/**
 * 主核心文件
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/3 0003
 * Time: 14:22
 */
class ICQueryParam
{

    protected $customConfig = null;

    public $keywordPortrait = null;

    public $analysisKeyword = null;

    public $ICPack = null;

    public $icsearch=null;

    public function __construct($customDirName,$keywordPortrait)
    {
        $this->keywordPortrait = $keywordPortrait;
        $this->ICPack=new ICPack();
        $this->analysisKeyword = new AnalysisKeyword();
        $this->customConfig= new CustomConfig($customDirName);
    }

    /**
     * @return array
     */

    public function recognizeKeywordQuery()
    {
        return $this->recognizeQuery($this->keywordPortrait->getKeyword('other'), ESQuery::OPERATOR_SHOULD, 1);
    }

    /**
     * @param bool $isMustHit
     * @return array
     */
    public function recognizeMfrQuery($isMustHit = false)
    {
        $queryParam = array();
        if ($this->keywordPortrait->getKeyword('mfr')) {
            $keyword_str = implode(" ", $this->keywordPortrait->getKeyword('mfr'));
            $tQueryParam = [];
            ESQuery::addBoolQuery($tQueryParam,$this->mfrQuery($keyword_str,ESQuery::OPERATOR_OR));
            ESQuery::addBoolQuery($tQueryParam,$this->descQuery($keyword_str));
            if ($tQueryParam) {
                $tList=[];
                $tList[]=ESQuery::constantScore(
                    ESQuery::boolQuery($tQueryParam,ESQuery::OPERATOR_SHOULD)
                    ,$this->customConfig->getBoost('mfr'));
                
                if (!$isMustHit) {
                    $tList[]=$this->verifyQuery();
                }
                $queryParam=ESQuery::boolQuery($tList);
            }

        }
        return $queryParam;
    }

    protected function verifyQuery(){
        return ESQuery::constantScore(
            ESQuery::termQuery($this->customConfig->s('extraVerify'),true)
            ,0);
    }


    public function mfrWeightsQuery($list)
    {
        $queryParam = array();
        if ($list) {
            $tQuery = [];
            foreach ($list as $item) {
                if (is_array($item['id'])) {
                    $tQuery[] = ESQuery::termsQuery($this->customConfig->s('mfrID'),$item['id'],$item['boost']);
                } else {
                    $tQuery[] = ESQuery::termQuery($this->customConfig->s('mfrID'),$item['id'],$item['boost']);
                }
            }
            if ($tQuery) {
                $queryParam = ESQuery::boolQuery(
                    [
                        ESQuery::boolQuery($tQuery),
                        $this->verifyQuery(),
                    ]
                );
            }
        }
        return $queryParam;

    }


    /**
     * @param bool $isMustHit
     * @return array
     */
    public function recognizeCateQuery($isMustHit = false)
    {
        $queryParam = array();
        $keyword_str = implode(" ", $this->keywordPortrait->getKeyword('cate'));
        if ($keyword_str) {
            $tQueryParam=[];
            ESQuery::addBoolQuery($tQueryParam,$this->cateQuery($keyword_str,ESQuery::OPERATOR_OR));
            ESQuery::addBoolQuery($tQueryParam,$this->descQuery($keyword_str));
            if ($tQueryParam) {
                $tQuery=[];
                $tQuery[]=ESQuery::constantScore(ESQuery::boolQuery($tQueryParam),$this->customConfig->getBoost('cate'));
                
                if (!$isMustHit) {
                    $tQuery[] = $this->verifyQuery();
                }
                $queryParam=ESQuery::boolQuery($tQuery);
            }
        }
        return $queryParam;
    }
    
    protected function cateQuery($keyword, $operator=ESQuery::OPERATOR_AND)
    {
        $t_query_param = [];
        $list = $this->customConfig->ss('cate');
        if ($list) {
            foreach ($list as $v) {
                ESQuery::addMatchQuery($t_query_param,$this->customConfig->fieldName($v),$keyword,$operator);
            }
        }
        return $t_query_param;
    }

    
    public function mfrQuery($keyword, $operator=ESQuery::OPERATOR_AND)
    {
        $t_query_param = [];
        $list = $this->customConfig->ss('mfr');
        if ($list) {
            foreach ($list as $v) {
                ESQuery::addMatchQuery($t_query_param,$this->customConfig->fieldName($v),$keyword,$operator);
            }
        }
        return $t_query_param;
    }


    protected function descQuery($keyword)
    {
        $queryParam = [];
        $descs = $this->customConfig->ss('desc');
        if ($descs) {
            foreach ($descs as $v) {
                ESQuery::addMatchQuery($queryParam,$this->customConfig->fieldName($v),$keyword,ESQuery::OPERATOR_AND);
            }
        }
        return $queryParam;
    }

    protected function expandKeywordsQuery(&$param,$keyword)
    {
        $expandFields = $this->customConfig->expandFields('keyword');
        if ($expandFields) {
            foreach ($expandFields as $item) {
                ESQuery::addMatchQuery($param,$this->customConfig->fieldName($item),$keyword,ESQuery::OPERATOR_AND);
            }
        }
    }


    public function recognizePartQuery()
    {
        $exactQuerys=$this->partExactQuerys();
        $prefixQuerys=$this->partPrefixQuerys();
        $tQuery = [];
        if($exactQuerys){
            $tQuery[]=$exactQuerys;
        }
        if($prefixQuerys){
            $tQuery[]=$prefixQuerys;
        }
        $queryParam = ESQuery::boolQuery($tQuery);
        return $queryParam;
    }

    /**
     * TODO 需保证不出现完全命中情况
     *
     * @return void
     */
    protected function partPrefixQuerys(){
        $queryParam = array();
        $keywordList=$this->partPrefixQueryKeywords($this->keywordPortrait->getKeyword('part'));
        $query=ESQuery::termsQuery($this->customConfig->s('partExtra'),$keywordList);
        if($query){
            $queryParam = ESQuery::constantScore($query,$this->customConfig->getBoost('prefixPart'));
        }
        return $queryParam;
    }


    protected function partExactQuerys(){
        $queryParam = array();
        $keywordList=$this->partExactQueryKeywords($this->keywordPortrait->getKeyword('part'));
        $query=ESQuery::termsQuery($this->customConfig->s('partFull'),$keywordList);
        if($query){
            $queryParam = ESQuery::constantScore($query,$this->customConfig->getBoost('exactPart'));
        }
        return $queryParam;
    }

    public function recognizeQuery($keywordList, $match, $minimum_should_match = null)
    {
        $queryParam = array();
        if ($keywordList) {
            $tList=[];
            foreach ($keywordList as $keyword) {
                $part_no_query = $this->partAnyHitQuery($keyword);
                $param=[];
                ESQuery::addMatchQuery($param,$this->customConfig->s('spec'),$keyword,ESQuery::OPERATOR_AND);
                ESQuery::addListQuery($param,$part_no_query);
                ESQuery::addBoolQuery($param,$this->cateQuery($keyword,ESQuery::OPERATOR_AND));
                ESQuery::addBoolQuery($param,$this->descQuery($keyword));
                $this->expandKeywordsQuery($param,$keyword);
                $tQuery=ESQuery::constantScore(ESQuery::boolQuery($param),$this->getKeywordBoost($keyword));
                ESQuery::addListQuery(
                    $tList
                    ,$tQuery
                ); 
            }
            $queryParam=ESQuery::boolQuery($tList,$match,$minimum_should_match);
        }
        return $queryParam;
    }

    private function getKeywordBoost($keyword){
        $keywordBoost = $this->customConfig->getBoost('boost.default');
        if ($this->ICPack->isNumberPack($keyword)) {
            $keywordBoost = $this->customConfig->getBoost('numberPack');
        } elseif ($this->ICPack->isLetterPack($keyword)) {
            $keywordBoost = $this->customConfig->getBoost('letterPack');
        } elseif ($this->analysisKeyword->isSpec($keyword)) {
            $keywordBoost = $this->customConfig->getBoost('letterPack');
        } elseif ($this->analysisKeyword->isMaterial($keyword)) {
            $keywordBoost = $this->customConfig->getBoost('material');
        }
        return $keywordBoost;
    }

    
    /**
     * 型号精确命中
     */
    protected function partExactQueryKeyword($keyword,$len=5)
    {
        $str=null;
        if (!$this->ICPack->isPack($keyword)
            && !$this->analysisKeyword->isCate($keyword)
            && !$this->analysisKeyword->ICMfr->isMfr($keyword)) {
            $tPart = Common::partNo($keyword);
            if ($tPart) {
                $vLen = mb_strlen($tPart);
                if ($vLen>$len || ($vLen < $len && $this->analysisKeyword->isShortPart($tPart))) {
                    $str=strtolower($tPart);
                }
            }
        }
        return $str;
    }

        /**
     * 型号精确命中
     */
    protected function partPrefixQueryKeyword($keyword,$len=5)
    {
        $str=null;
        if (!$this->ICPack->isPack($keyword)
            && !$this->analysisKeyword->isCate($keyword)
            && !$this->analysisKeyword->ICMfr->isMfr($keyword)) {
            $tPart = Common::partNo($keyword);
            if ($tPart) {
                $vLen = mb_strlen($tPart);
                if ($vLen >= $len) {
                    $str=strtolower($tPart);
                }
            }
        }
        return $str;
    }

    /**
     * 型号前缀命中
     */
    protected function partPrefixQueryKeywords($list,$len=5)
    {
        $res=[];
        foreach ($list as $v) {
            $t=$this->partPrefixQueryKeyword($v,$len);
            if($t){
                $res[]=$t; 
            }
        }
        return Common::icArrayIunique($res);
    }


            /**
     * 型号精确命中
     */
    protected function partExactQueryKeywords($list,$len=5)
    {
        $res=[];
        foreach ($list as $v) {
            $t=$this->partExactQueryKeyword($v,$len);
            if($t){
                $res[]=$t; 
            }
        }
        return Common::icArrayIunique($res);
    }


    /**
     * 型号任意命中
     */

    protected function partAnyHitQuery($keyword)
    {
        $partQuery = array();
        $t_part = Common::partNo($keyword);
        if (!Common::hasChinese($keyword)
            && !$this->analysisKeyword->isShortPart($t_part)
        )
        {
            if (!($this->analysisKeyword->isSpec($keyword) && strpos($keyword,'.')!==false )){
                $partQuery = ESQuery::matchQuery($this->customConfig->s('part'),$keyword,ESQuery::OPERATOR_AND);
            }

        }
        return $partQuery;
    }

    //弃用
    public function filterMainQuery()
    {
        $query = array();
        $partQuery = $this->recognizePartQuery();
        $keywordQuery = $this->filterKeywordQuery();
        if ($partQuery) {
            $t[] = $partQuery;
        }
        if ($keywordQuery) {
            $t[] = $keywordQuery;
        }
        if (!empty($t)) {
            $query=ESQuery::boolQuery([ESQuery::boolQuery($t)],ESQuery::OPERATOR_MUST);
        }
        return $query;
    }

    public function filterKeywordQuery()
    {
        $query_param = array();
        $keywordList = $this->keywordPortrait->getKeyword('other');
        if ($keywordList) {
            $must_list = [];
            $should_list = [];
            //初步筛选
            $unit = 'pf|uf|f|pt|nf|mf|ohms|ohm|kohms|kohm|mohms|mohm|r|uh|nh|h|mh|mhz|khz';
            foreach ($keywordList as $v) {
                if ($this->ICPack->isNumberPack($v) || $this->analysisKeyword->isSpec($v, $unit)) {
                    $must_list[] = $v;
                } else {
                    $should_list[] = $v;
                }
            }
            $t=[];
            if ($must_list) {
                $t[] = $this->recognizeQuery($must_list, ESQuery::OPERATOR_MUST, null);
            }
            if ($should_list){
                $t[] = $this->recognizeQuery($should_list, ESQuery::OPERATOR_SHOULD, null);
            }
            $query_param=ESQuery::boolQuery($t,ESQuery::OPERATOR_MUST);
        }
        return $query_param;
    }



    /**
     * 品牌加权
     * @param array $param
     * @return array
     */
    public function mfrBoostQuery($param = array())
    {
        //如果在特定分类下，给对应制造商加不同权限，如没有，则取系统默认值
        $mfrBoostQuery = array();
        if ($this->keywordPortrait->isResistor()) {
            $mfrBoostQuery = $this->mfrWeightsQuery(Common::arrayGet($param, 'resistor', $this->customConfig->mfrResistor()));
        } else if ($this->keywordPortrait->isCapacitor()) {
            $mfrBoostQuery = $this->mfrWeightsQuery(Common::arrayGet($param, 'capacitor', $this->customConfig->mfrCapacitor()));
        } else if ($this->keywordPortrait->isInductance()) {
            $mfrBoostQuery = $this->mfrWeightsQuery(Common::arrayGet($param, 'inductance', $this->customConfig->mfrInductance()));
        } else if ($this->keywordPortrait->isCrystalOscillator()) {
            $mfrBoostQuery = $this->mfrWeightsQuery(Common::arrayGet($param, 'crystal_oscillator', $this->customConfig->mfrCrystalOscillator()));
        } else if ($this->keywordPortrait->isConnector()) {
            $mfrBoostQuery = $this->mfrWeightsQuery(Common::arrayGet($param, 'connector', $this->customConfig->mfrConnector()));
        }
        return $mfrBoostQuery;

    }

        /**
     * 从mfr_boost里过滤特定分类的制造商权重
     * @param $mfrOrders
     * @return array
     */
    public function filterMfrBoostQueryParam($mfrOrders=array()){
        $res=[];
        if ($mfrOrders){
            foreach ($mfrOrders as $k=>$v){
                $arr=explode(",",$v);
                $key=null;
                $list=[];
                    switch ($k){
                        case 'capacitor':
                            $key='capacitor';
                            $list=$this->customConfig->mfrCapacitor();
                            break;
                        case 'resistor':
                            $key='resistor';
                            $list=$this->customConfig->mfrResistor();
                            break;
                        case 'inductance':
                            $key='inductance';
                            $list=$this->customConfig->mfrInductance();
                            break;
                        case 'crystal_oscillator':
                            $key='crystal_oscillator';
                            $list=$this->customConfig->mfrCrystalOscillator();
                            break;
                    }
                    if ($key && $arr && $list){
                        foreach ($arr as $item){
                            $t=array_get($list,$item,null);
                            if ($t){
                                $res[$key][$t['code']]=$t;
                            }
                        }
                    }
                }
        }
        return $res;
    }


}


