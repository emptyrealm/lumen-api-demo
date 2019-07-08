<?php

namespace ICSearch;

use ICSearch\libs\Common;
use ICAnalysis\ICPack\ICPack;

/**
 * 工具
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/3 0003
 * Time: 14:22
 */
class ICTool
{

    /**
     * 
     */
 
    public function getPackByKeyword($keyword){
        $res=[];
        if($keyword){
            Common::loadLib('partanalysis/PartAnalysis');
            $partAnalysis = new \PartAnalysis('utf-8', 'utf-8', true);
            $partAnalysis->LoadDict();
            $partAnalysis->SetSource($keyword);
            $partAnalysis->StartAnalysis(false);
            $tt_keyword_list = $partAnalysis->getAnalysisResult();
            $keywordList=Common::getKeywordByAnalysisResult($tt_keyword_list);
            $res=$this->getPackByKeywordList($keywordList);
        }
        return $res;
    }

    public function getPackByKeywordList($keywordList){
        $res=[];
        if($keywordList){
            $ICPack=new ICPack();
            foreach($keywordList as $value){
                if($ICPack->isPack($value)){
                    $res[]=$value;
                }
            }
        }
        return Common::icArrayIunique($res);
    }



}


