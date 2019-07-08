<?php

namespace ICSearch;

use ICSearch\libs\Common;
use function GuzzleHttp\json_decode;
use ICAnalysis\ICMfr\ICMfr;
use ICSearch\libs\ESQuery;

/**
 * web搜索
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/3 0003
 * Time: 14:22
 */
class WebSearch extends ICSearch
{
    /**
     * Undocumented function
     *
     * @param [type] $condition keyword
     * @param integer $page
     * @param integer $pageSize
     * @param array $sorts
     * @param array $aggs
     * @return void
     */
    public function initQuery($condition,$page=1, $pageSize=10,$sorts=array(),$aggs=array()){
        $mainQuery=[];
        $this->standardModel($condition);
        $webQueryParam=new WebQueryParam($this->customDirName(),$this->keywordPortrait);
        $mainQueryParam = $webQueryParam->matchQueryModel();
        if (!empty($mainQueryParam)) {
            $mainQuery['bool']['must'][] = $mainQueryParam;
        }
        if ($mainQuery){
            //主语句
            $queryParam=array();
            if (!empty($mainQuery)) {
                $queryParam['bool']['must'][] = $mainQuery;
                //过滤
                // $filterMainQuery=$webQueryParam->filterMainQuery();
                // if ($filterMainQuery){
                //     $queryParam['bool']['filter']['bool']['must'][]=$filterMainQuery;
                // }
                $mfrFilter=$this->mfrFilterByKeyword($this->getMfrIDsByKeyword());
                if($mfrFilter){
                    $queryParam['bool']['filter']['bool']['must'][]=$mfrFilter;
                }
            }
            if($queryParam){
                $this->customQuery($queryParam,$page,$pageSize,$sorts,$aggs);
            }

        }
    }

    private function mfrFilterByKeyword($ids){
        if($ids){
            return ESQuery::termsQuery($this->customConfig->s('mfrID'),$ids);
        }
    }


    private function getMfrIDsByKeyword(){
        $mfrs=$this->keywordPortrait->getKeyword('mfr');
        $synonyms=$this->keywordPortrait->getKeyword('synonym');
        $mfrIdFileKey=$this->customConfig->config('mfrIdFileKey');
        $ICMfr=new ICMfr();
        $ids=[];
        if($mfrs){
            foreach($mfrs as $mfr){
                if($mfr){
                    $t=[];
                    $t[]=$mfr; 
                    if(isset($synonyms[$mfr])){
                        $t=array_merge($t,$synonyms[$mfr]['synonym']);
                    }
                    if($t){
                        foreach($t as $v){
                            $tId=$ICMfr->getMfrID($mfrIdFileKey,$v);
                            if($tId){
                                $ids[]=$tId;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $ids;
    }


    public function initScoreQuery($condition,$scoreFcuntions=array(),$page=1, $pageSize=10,$sorts=array(),$aggs=array()){
        $mainQuery=[];
        $this->standardModel($condition);
        $webQueryParam=new WebQueryParam($this->customDirName(),$this->keywordPortrait);
        $mainQueryParam = $webQueryParam->matchQueryModel();
        if (!empty($mainQueryParam)) {
            $mainQuery['bool']['must'][] = $mainQueryParam;
        }
        if ($mainQuery){
            //主语句
            $queryParam=array();
            if (!empty($mainQuery)) {
                $queryParam['bool']['must']['function_score']['query'] = $mainQuery;
                $queryParam['bool']['must']['function_score']['functions']=$scoreFcuntions;
                //过滤
                // $filterMainQuery=$webQueryParam->filterMainQuery();
                // if ($filterMainQuery){
                //     $queryParam['bool']['filter']['bool']['must'][]=$filterMainQuery;
                // }
            }
            if($queryParam){
                // echo json_encode($queryParam);exit;
                $this->customQuery($queryParam,$page,$pageSize,$sorts,$aggs);
            }

        }
    }

    public function addFilterQuery($param){
        if($param){
            $this->query_param['body']['query']['bool']['filter']['bool']['must'][]=$param;
        }
    }

    public function query(){
        // echo json_encode($this->query_param);
        // exit;
        $this->search();
        $this->getKeywordList();
    }


    public function customQuery($queryParam,$page=1, $pageSize=10,$sorts=array(),$aggs=array()){
        if($queryParam){
            $this->setQuery($queryParam);
            $this->setHighlight($this->getHighlightKeyList());
            $this->setLimit($page, $pageSize);
            $this->setAggs($aggs);
            $this->setSort($sorts);
        }
    }
  
}


