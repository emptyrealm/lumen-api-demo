<?php

namespace ICSearch;

use ICSearch\libs\Common;
use ICSearch\libs\AnalysisKeyword;
use ICSearch\libs\ESQuery;

/**
 * bom识别
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/3 0003
 * Time: 14:22
 */
class BomMatch extends ICSearch
{
    
    public function initQuery($condition,$page=1, $pageSize=30,$order=array()){
        $this->formatCondition($condition);
        $this->standardModel($condition);
        $bomQueryParam=new BomQueryParam($this->customDirName(),$this->keywordPortrait);
        $mainQueryParam = $bomQueryParam->matchQueryModel();
        if ($mainQueryParam){
            //主语句
            $query_param=array();
            $query_param['bool']['must'][] = $mainQueryParam;
                //过滤
            // $filterMainQuery=$bomQueryParam->filterMainQuery();
            // if ($filterMainQuery){
            //     $query_param['bool']['filter']['bool']['must'][]=$filterMainQuery;
            // }
            //mfr 加权
            $isBoost=false;
            $mfrOrders=$this->getCond('mfr_orders',array());
            $mfrBoostQueryParam=array();
            if ($mfrOrders){
                $mfrBoostQueryParam=$bomQueryParam->filterMfrBoostQueryParam($mfrOrders);
                $isBoost=true;
            }elseif (!$this->keywordPortrait->getKeyword('mfr')){
                $isBoost=true;
            }
            if ($isBoost){
                $mfrBootQuery=$bomQueryParam->mfrBoostQuery($mfrBoostQueryParam);
                if($mfrBootQuery){
                    $query_param['bool']['must'][] = $bomQueryParam->mfrBoostQuery($mfrBoostQueryParam);
                }
            }
            if($query_param){
                $this->setHighlight($this->getHighlightKeyList());
                $this->setQuery($query_param);
                $this->setLimit($page, $pageSize);
                $this->setSort($order);
            }

        }
    }


    public function addFilterQuery($param){
        if($param){
            $this->query_param['body']['query']['bool']['filter']['bool']['must'][]=$param;
        }
    }


    public function query(){
        $this->search();
    }

    //筛选结果
    public function funnel(){
        
        $this->filterSearch();
    
    }

    //处理分数
    public function promote(){
        // $this->processScore();
    }

    // private function processScore(){
    //     if ($this->getResult()){
    //         foreach ($this->getResult() as $key=>$item){
    //             $source=$item['_source'];
    //             $source['_sortScore']=$item['_score'];
    //             $this->updateResultSource($key,$source);
    //         }
    //     }
    // }
    

    public function updateSku($key,$data){
        if($this->customConfig->s('sku') && is_array($data)){
            $this->result[$key]['_source'][$this->customConfig->s('sku')]=$data;
        }
    }

    public function formatCondition(&$condition){
        $keyword = Common::arrayGet($condition, 'keyword', '');
        $cate = Common::arrayGet($condition, 'cate', '');
        $mfr = Common::arrayGet($condition, 'mfr', '');
        $keyword_list = explode("||", ltrim(trim($keyword), "||"));
        $keyword_list[] = $cate;
        $keyword_list[] = $mfr;
        $condition['keyword']=implode(" ",$keyword_list);
    }


    /**
     * TODO 待移动到BomMatch
     *
     * @return void
     */
    public function filterSearch()
    {
        foreach ($this->getResult() as $key => $doc) {
            if($doc){
                $id = Common::arrayGet($doc, '_id', 0);
                $tPart = Common::arrayGet($doc, '_source.' . $this->customConfig->s('part'), '');
    
                $tHitKeyword = $this->keywordPortrait->getKeyword('hitKeyword.'.$id);
                $tHitPartKeyword = $this->_getHitPart($id);
                $isPass = true;

                //判断所有命中词是否没有包含数字
                if (!Common::hasNumber(implode(" ",$tHitKeyword).' '.$tHitPartKeyword)){
                    $isPass = false;
                }
                //如果完全命中则直接通过检查
                if ($isPass && !($this->_part_no($tHitPartKeyword) == $this->_part_no($tPart))) {
                    
                    $mustHitByCate=$this->checkMustHitByCate($doc);
                    if(!$mustHitByCate){
                        $isPass = false;
                    }
                    
                    //如果命中词只有一个并且沒有命中型号，则抛弃
                    if(count($tHitKeyword)<=1 && empty($tHitPartKeyword)){
                        $isPass = false;
                    }
                    //如果检查通过，则继续检查型号命中
                    if ($isPass) {
                        if (empty($tHitPartKeyword)) {
                            //没有命中型号,并且必中项爷没有命中
                            if (!$mustHitByCate) {
                                $isPass = false;
                            }
                        } else {
                            if (count($tHitKeyword) <= 1) {
                                //有命中型号
                                if (Common::isEnglish(Common::partNo($tHitPartKeyword))) {
                                    //全字母字符
                                    $isPass = false;
                                }
                                if ($this->ICPack->isPack($tHitPartKeyword)) {
                                    //是封装
                                    $isPass = false;
                                }
                            }
    
                        }
                    }
                }
                if (!$isPass) {
                    unset($this->result[$key]);
                }
            }
        }
        $this->result = $this->result;
    }

    private function _getHitPart($id){
        return $this->keywordPortrait->getKeyword('hitPartKeyword.'.$id.'.0',null);
    }

    /**
     * TODO 待移动到BomMatch
     * 特定分类必中项要命中
     */
    private function checkMustHitByCate($doc){
        $id=$doc['_id'];
        $isPass=true;
        $hitKeywordList=$this->keywordPortrait->getKeyword('hitKeyword.'.$id,array());
        //检查是否有必中项要命中
        
        $packRes = $this->_mustHitPackKeyword(
            $this->keywordPortrait->getKeyword('list'),
            $hitKeywordList
        );
        //电阻 //ohms|ohm|kohms|kohm|mohms|mohm
        if ($this->keywordPortrait->isResistor()) {
            $mustRes = $this->_mustHitSpecKeyword(
                $this->keywordPortrait->getKeyword('list'),
                $hitKeywordList,
                'ohms|ohm|kohms|kohm|mohms|mohm|r'
            );
        } else if ($this->keywordPortrait->isCapacitor()) {
            //电容 //pf|uf|f|pt|nf|mf
            $mustRes = $this->_mustHitSpecKeyword(
                $this->keywordPortrait->getKeyword('list'), 
                $hitKeywordList,
                'pf|uf|f|pt|nf|mf'
            );
        } else if ($this->keywordPortrait->isCrystalOscillator()) {
            //晶振 //mhz|khz
            $mustRes = $this->_mustHitSpecKeyword(
                $this->keywordPortrait->getKeyword('list'),
                $hitKeywordList,
                'mhz|khz'
            );
        } else if ($this->keywordPortrait->isInductance()) {
            //晶振 //uh|nh|h|mh
            $mustRes = $this->_mustHitSpecKeyword(
                $this->keywordPortrait->getKeyword('list'),
                $hitKeywordList,
                'uh|nh|h|mh'
            );
        } else {
            $mustRes = true;//如果没有分类，默认为true
        }

        //任一结果为false
        if (!$packRes || !$mustRes) {
            $isPass = false;
        }
        return $isPass;
    }

        /**
     * @param $allKeyword
     * @param $hitKeyword
     * @param $unit
     * @return int
     */
    private function _mustHitSpecKeyword($allKeyword, $hitKeyword, $unit)
    {
        return $this->_mustHitKeyword($allKeyword, $hitKeyword, 'spec', array('unit' => $unit));
    }

    
    /**
     * @param $allKeyword
     * @param $hitKeyword
     * @return int
     */
    private function _mustHitPackKeyword($allKeyword, $hitKeyword)
    {
        return $this->_mustHitKeyword($allKeyword, $hitKeyword, 'pack');
    }

       /**
     * 1 没有进行匹配流程
     * 2 进行匹配流程并且成功
     * 0 进行匹配流程并且无结果
     * @param $allKeyword
     * @param $hitKeyword
     * @param $type
     * @param array $param
     * @return int
     */
    private function _mustHitKeyword($allKeyword, $hitKeyword, $type, $param = array())
    {
        $res = 1;
        $tMustHit = array();
        if (empty($hitKeyword)) {
            $res = 1;
        }
        $AnalysisKeyword=new AnalysisKeyword();
        if ($allKeyword && is_array($allKeyword)) {
            foreach ($allKeyword as $v) {
                switch ($type) {
                    case 'pack':
                        if ($this->ICPack->isNumberPack($v)) {
                            $res = 0;
                            $tMustHit[] = $v;
                        }
                        break;
                    case 'spec':
                        if ($AnalysisKeyword->isSpec($v, $param['unit'])) {
                            $res = 0;
                            $tMustHit[] = $v;
                        }
                        break;
                }
            }
            if (empty($tMustHit)) {
                $res = 1;
            }
//            echo "<pre>";
//            print_r($tMustHit);
//            echo "</pre>";
            if ($this->_arrayHit($hitKeyword, $tMustHit)) {
                $res = 3;
            }
        }
        return $res;
    }

    
    //判断array2是否全部在array1中全部命中，不区分大小写
    private function _arrayHit($array1, $array2)
    {
        $i = 0;
        if ($array1 && $array2) {
            foreach ($array2 as $v2) {
                foreach ($array1 as $v1) {
                    if (strtolower($v1) == strtolower($v2)) {
                        $i++;
                        break;
                    }
                }
            }
        }
        if ($i == count($array2)) {
            return true;
        }
        return false;

    }




  
}


