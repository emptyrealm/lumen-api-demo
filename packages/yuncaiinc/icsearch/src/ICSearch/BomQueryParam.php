<?php

namespace ICSearch;

use ICSearch\libs\Common;
use ICSearch\libs\ESQuery;

/**
 * 主核心文件
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/3 0003
 * Time: 14:22
 */
class BomQueryParam extends ICQueryParam
{

    public function matchQueryModel()
    {
        $mfrIsMustHit = false;
        $cateIsMustHit = false;
        $part_query_param = $this->recognizePartQuery();
        $keyword_query_param = $this->recognizeKeywordQuery();
        $main_query_param = array();

        #TODO 无法确认$part_query_param里的关键词是型号，在没有$keyword_query_param会导致$part_query_param条件必中，造成无结果情况
        if ($part_query_param) {
            $t['bool']['should'][] = $part_query_param;
        }
        if ($keyword_query_param) {
            $t['bool']['should'][] = $keyword_query_param;
        }
        if (!empty($t)) {
            $main_query_param['bool']['must'][] = $t;
        } else {
            //如果关键词和型号sql都没有，则分类或者制造商都需命中
            $mfrIsMustHit = true;
            $cateIsMustHit = true;
        }

        $mfr_query_param = $this->recognizeMfrQuery($mfrIsMustHit);
        if ($mfr_query_param) {
            $main_query_param['bool']['must'][] = $mfr_query_param;
        }

        $cate_query_param = $this->recognizeCateQuery($cateIsMustHit);
        if ($cate_query_param) {
            $main_query_param['bool']['must'][] = $cate_query_param;
        }
        return $main_query_param;
    }



    /**
     * 覆盖父类
     *
     * @param [type] $keyword
     * @return void
     */
    protected function partAnyHitQuery($keyword)
    {
        $partQuery = array();
        $t_part = Common::partNo($keyword);
        if (!Common::hasChinese($keyword)
            && mb_strlen($t_part) <= 6 && mb_strlen($t_part) >= 3
            && !$this->analysisKeyword->isShortPart($t_part)
        )
        {
            if (!($this->analysisKeyword->isSpec($keyword) && strpos($keyword,'.')!==false )){
                $partQuery = ESQuery::matchQuery($this->customConfig->s('part'),$keyword,ESQuery::OPERATOR_AND);
            }
        }
        return $partQuery;
    }
    
}


