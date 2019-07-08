<?php

namespace ICSearch;

use ICSearch\libs\BomKeyword;
use ICSearch\libs\ChineseConvert;
use ICSearch\libs\Common;
use ICSearch\libs\KeywordPortrait;
use ICSearch\libs\AnalysisKeyword;
use ICAnalysis\ICPack\ICPack;
use ICSearch\libs\CustomConfig;
use ICAnalysis\ICMfr\ICMfr;

/**
 * 主核心文件
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/3 0003
 * Time: 14:22
 */
class ICSearch extends BaseSearch
{

    private $highlightKeyList = array();

    private $bomKeyword = null;

    private $customDirName = null;

    protected $keywordPortrait = null;

    protected $ICPack=null;
    
    public $customConfig = null;

    /**
     * ICSearch constructor.
     * @param $name
     * @param $connections
     *
     *
     ['connections' => [
        'hosts' => [
            [
                'host' => '120.76.46.169',
                'port' => '9200',
                'scheme' => 'http',
                'user' => 'elastic',
                'pass' => 'vQvG6r6LA',
            ]
        ],
        'index' => 'fi_v_pro_alias',
        'type' => 'fi_v_column',
        ]
     ]
     */
    public function __construct($name,$connections=array())
    {
        $AnalysisKeyword=new AnalysisKeyword();
        $this->keywordPortrait=new KeywordPortrait($AnalysisKeyword);
        $this->customDirName = $name;
        $this->bomKeyword = new BomKeyword($AnalysisKeyword,$this->keywordPortrait);
        $this->ICPack=new ICPack();
        $this->customConfig= new CustomConfig($name);
        $this->init($connections);
    }

    protected function customDirName(){
        return $this->customDirName;
    }

    private function init($connections)
    {
        $this->config = Common::custom($this->customDirName . '/app');
        $this->config['connections']=$connections;
        $this->initSearch($this->config['connections']);
        $this->setHighlightKeyList();
    }

    private function setHighlightKeyList()
    {
        $default = $this->defaultHighlightKey();
        $expandFields = $this->customConfig->expandFields('keyword');
        if ($expandFields) {
            foreach ($expandFields as $field) {
                if (!empty($field['highlight'])) {
                    $default[] = $field['field'];
                }
            }
        }
        $this->highlightKeyList = $default;
    }

    private function defaultHighlightKey()
    {
        $res = [];
        foreach ($this->customConfig->getField() as $item) {
            $t = $this->_highlightKey($item);
            $res = array_merge($res, $t);
        }
        return $res;
    }

    private function _highlightKey($item)
    {
        $res = array();
        if (isset($item['fields'])) {
            foreach ($item['fields'] as $field) {
                if (!empty($field['highlight'])) {
                    $res[] = $field['field'];
                }
            }
        } else {
            if (!empty($item['highlight'])) {
                $res[] = $item['field'];
            }
        }
        return $res;
    }


    private function getHitPartKeyword($highlight)
    {
        if($highlight){
            if (preg_match_all('#'.$this->highlightPreTags . '(.*?)' . $this->highlightPostTags."#", $highlight[0], $arr)) {
                return implode("",Common::arrayGet($arr, 1, array()));
            }
        }
    }


    protected function setHitKeyword()
    {
        $this->setHitCommonKeyword();
        $this->setHitPartKeywords();
    }

    private function setHitPartKeywords()
    {
        $hitPartKeyword = [];
        foreach ($this->gethighlightList() as $k=>$item) {
            $t=null;
            if(!empty($item[$this->customConfig->s('partFull')])){
                $t=$this->getHitPartKeyword($item[$this->customConfig->s('partFull')]);
            }else if(!empty($item[$this->customConfig->s('partExtra')])){
                $t=$this->getHitPartKeyword($item[$this->customConfig->s('partExtra')]);
            }
            if(!empty($t)){
                $hitPartKeyword[$k][] = $t;
            }
        }
        $this->keywordPortrait->setKeyword('hitPartKeyword', $hitPartKeyword);
    }

    private function setHitCommonKeyword()
    {
        $res = [];
        foreach ($this->gethighlightList() as $key=>$item) {
            $hitPart=null;
            if(isset($item[$this->customConfig->s('partExtra')])){
                unset($item[$this->customConfig->s('partExtra')]);
            }
            if(isset($item[$this->customConfig->s('partFull')])){
                unset($item[$this->customConfig->s('partFull')]);
            }
            if(isset($item[$this->customConfig->s('part')])){
                $hitPart=$this->getHitPartKeyword($item[$this->customConfig->s('part')]);
                unset($item[$this->customConfig->s('part')]);
            }
            if (!empty($item)) {
                $res[$key] = $this->keywordPortrait->getHitKeywordList($item);
            }
            if(!empty($hitPart)){
                $res[$key][]=$hitPart;
            }
            if(isset($res[$key])){
                $res[$key]=Common::icArrayIunique($res[$key]);
            }
        }
        $this->keywordPortrait->setKeyword('hitKeyword', $res);
    }



    protected function getHighlightKeyList(){
        return $this->highlightKeyList;
    }

    public function getKeywordList(){
        return $this->keywordPortrait->getKeywordList();
    }

    public function getKeyword($key,$default=array())
    {
        return $this->keywordPortrait->getKeyword($key,$default);
    }


    protected function standardModel($condition){
        $this->setCondition($condition);

        $this->log->msg('原始参数', $this->condition);

        $keyword=$this->getCond('keyword');
        $design=$this->getCond('design');

        $this->log->msg('关键词', $keyword);

        $cateKeyword = $this->collectCateKeywordByKeyword($keyword);

        $this->log->msg('提取的分类', $cateKeyword);

        $keywordList=$this->standardAnalyzer($keyword);

        $this->log->msg('标准分析结果', $keywordList);
        
        if($cateKeyword){
            $keywordList[]=$cateKeyword;
        }

        //设置位号
        $this->keywordPortrait->_setDesign($design);
        //分类识别
        $this->identifyCategory($keyword,$this->keywordPortrait->getKeyword('design'));
        
        $this->log->msg('分类识别', $this->keywordPortrait->cateCode);

        //根据分类处理分类词
        $keywordList = $this->bomKeyword->setKeywordListByCateList($keywordList);
        $keywordList = $this->tokenFilters($keywordList);

        $mfrKeywordList=$this->collectMfrByList($keywordList);
        $cateKeywordList=$this->collectCateKeywordByList($keywordList,[$cateKeyword]);
        $partKeywordList=$this->collectPartKeywordByList($keyword);

        //设置keywordPortrait
        $this->keywordPortrait->setKeyword('mfr', $mfrKeywordList);
        $this->keywordPortrait->setKeyword('list', $keywordList);
        $this->keywordPortrait->setKeyword('analysis', $keywordList);
        $this->keywordPortrait->setKeyword('cate', $cateKeywordList);
        $this->keywordPortrait->setKeyword('part', array_values($partKeywordList));
        $otherKeywordList = array_diff($keywordList, $cateKeywordList, $mfrKeywordList);
        $this->keywordPortrait->setKeyword('other', array_values($otherKeywordList));
        $searchKeywordList = Common::icArrayIunique(
            array_merge(
                $this->keywordPortrait->getKeyword('other'),
                $this->keywordPortrait->getKeyword('mfr'),
                $this->keywordPortrait->getKeyword('cate')
            )
        );
        $this->keywordPortrait->setKeyword('search', $searchKeywordList);
        $this->keywordPortrait->setKeyword(
            'synonym',
            $this->keywordPortrait->getKeywordSynonym($searchKeywordList)
        );
        $this->log->msg('关键词表', $this->keywordPortrait->getKeywordList());
    }

    private function collectPartKeywordByList($keyword){
        $keywordList=$this->tokenizers($keyword);
        return $this->bomKeyword->getPartQueryKeywordList( $keywordList);
    }

    /**
     * 分类识别
     *
     * @param [type] $keyword
     * @param [type] $keywordList
     * @param [type] $design
     * @return void
     */
    private function identifyCategory($keyword,$design){
        $keywordList=$this->tokenizers($keyword);
        //根据现在关键词识别分类
        $this->_identifyCategory($keywordList,$design);
        //根据分类处理关键词
        $keywordList = $this->bomKeyword->setKeywordListByCateList($keywordList);
        //根据新的关键词识别分类
        $this->_identifyCategory($keywordList,$design);
    }


    
    private function _identifyCategory($keywordList,$design)
    {
        $this->keywordPortrait->setCateResistor($keywordList, $design);
        $this->keywordPortrait->setCapacitor($keywordList, $design);
        $this->keywordPortrait->setInductance($keywordList);
        $this->keywordPortrait->setCrystalOscillator($keywordList);
        $this->keywordPortrait->setConnector($keywordList);
        $this->keywordPortrait->setMos($keywordList);
    }



    //标准分析器
    protected function standardAnalyzer($keyword){
        $keywordList=[];
        if($keyword){
            $keyword = $this->characterFilters($keyword);
            $keywordList=$this->tokenizers($keyword);
            $keywordList=$this->tokenFilters($keywordList);
        }
        return $keywordList;
    }

    private function collectCateKeywordByList($keywordList,$curCategoryList){
        return array_values($this->bomKeyword->getCateList($curCategoryList, $keywordList));
    }


    private function collectCateKeywordByKeyword($keyword){
        return $this->bomKeyword->getCateByKeyword($keyword);
    }

    private function collectMfrByList($keywordList){
        return array_values($this->bomKeyword->getMfrList($keywordList));
    }


    //字符清洗
    protected function characterFilters($keyword){
        if($keyword){
            $keyword = Common::charsetToUtf8($keyword);
            $keyword = $this->toSimplified($keyword);
            $keyword = $this->bomKeyword->processBomKeywordByKeyword($keyword);
        }
        return $keyword;
    }

    protected function toSimplified($keyword){
        $chineseConvert=new ChineseConvert();
        return $chineseConvert->toSimplified($keyword);
    }

    //分词器
    protected function tokenizers($keyword){
        $res=[];
        $keyword = trim($keyword);
        if ($keyword && $this->client()) {
            $analyzerRes = $this->client()->indices()->analyze([
                "index"=>$this->indexName(),
                "analyzer"=>'desc_analyzer',
                "text"=>$keyword,
            ]);
            if($analyzerRes){
                foreach($analyzerRes['tokens'] as $item){
                    $res[]=$item['token'];
                }
            }
        }
        return $res;
    }

    //分词过滤器

    protected function tokenFilters($keywordList){
        $keywordList = $this->bomKeyword->processBomKeywordList($keywordList);
        //删除无意义字符
        $keywordList = $this->bomKeyword->removeInvalidUnit($keywordList);
        //删除关键词中的位号
        $keywordList = $this->bomKeyword->removeDesign($keywordList);
        //过滤关键词
        $keywordList = $this->bomKeyword->filterBomKeywordByList($keywordList);
        //去重
        $keywordList = Common::icArrayIunique($keywordList);
        return $keywordList;
    }

    public function isFullHitPartById($id){
        $res=false;
        $data=Common::arrayGet($this->getResult(),$id,array());
        if($data){
            $tPart=Common::arrayGet($data,'_source.'.$this->customConfig->s('part'));
            $hitPartKeyword=$this->keywordPortrait->getKeyword('hitPartKeyword.'.$id.'.0','');
            if($tPart && $hitPartKeyword){
                if(Common::partNo($hitPartKeyword) == Common::partNo($tPart) ){
                    $res=true;
                }
            }
        }
        return $res;
    }


 








}


