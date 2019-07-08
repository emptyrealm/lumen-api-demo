<?php

namespace ICSearch;

use ICSearch\libs\Common;
use Elasticsearch\ClientBuilder;
use ICSearch\libs\Log;
use function GuzzleHttp\json_encode;

class BaseSearch
{

    protected $result = array(); //TODO private
    private $source = array();
    private $resultTotal = 0;
    private $highlightList = array();

    private $resultSource = array();

    private $resultIds = array();
    private $aggregations = array();

    protected $condition = array(); //TODO private

    protected $highlightPreTags = '<em class="i_key">';
    protected $highlightPostTags = '</em>';

    //elasticsearch
    protected $client = null;
    protected $index_name = null;
    protected $column_type = null;
    
    protected $query_param = array();

    public $log = null;

    public function initSearch($config)
    {
        $this->log = new Log();
        if ($config) {
            $this->client = ClientBuilder::create()->setHosts($config['hosts'])->build();
            $this->index_name = $config['index'];
            $this->column_type = $config['type'];
            $this->query_param = [
                'index' => $this->index_name,
                'type' => $this->column_type,
                'body' => [],
                'explain' => false,//测试专用，请勿在正式使用
            ];
        }
    }

    protected function indexName(){
        return $this->index_name;
    }

    protected function client(){
        return $this->client;
    }

    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @param $text
     * @param string $analyzer ik_max_word ik_smart
     * @return array
     */
    public function chineseWordSegmentation($text, $analyzer = 'ik_smart')
    {
        $text = trim($text);
        if ($text && $this->client) {
            $param['index'] = $this->index_name;
            $param['analyzer'] = $analyzer;
            $param['text'] = $text;
            $res = $this->client->indices()->analyze($param);
            return !empty($res['tokens']) ? $res['tokens'] : array();
        }
        return array();
    }

    /**
     * 执行搜索
     */
    public function search()
    {
        $this->source = $this->_search();
        $this->resultTotal = $this->source['hits']['total'];
        $this->aggregations = !empty($this->source['aggregations']) ? $this->source['aggregations'] : array();
        $this->setResult();
        $this->setHitKeyword();
    }


    public function _search()
    {
        return $this->client->search($this->query_param);
    }

    public function setResult()
    {
        if ($this->source['hits']['hits']) {
            foreach ($this->source['hits']['hits'] as $item) {
                $this->result[$item['_id']]= $item;
                $this->resultSource[$item['_id']]= $item['_source'];
                $this->resultIds[] = $item['_id'];
                if (!empty($item['highlight'])) {
                    $this->setHighlightList($item['_id'], $item['highlight']);
                }
            }
        }
    }

    protected function setHitKeyword(){

    }


    public function setHighlightList($id, $highlight)
    {
        $this->highlightList[$id] = $highlight;
    }



    public function _formatKeyword($keyword)
    {
        return preg_replace("/\s(?=\s)/i", "\\1", trim(urldecode($keyword)));
    }

    public function setLimit($page = 1, $page_size = 10)
    {
        $page = !empty($page) ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $this->query_param['body']['from'] = $offset;
        $this->query_param['body']['size'] = $page_size;
    }

    public function test($data = array(), $stop = true)
    {
        $data = empty($data) ? $this->query_param : $data;
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if ($stop) {
            exit;
        }
    }

    public function formatKeyword($str, $is_lower = false)
    {
        $str = trim(urldecode($str));
        if ($is_lower) {
            $str = strtolower($str);
        }
        return $str;
    }


    public function defaultHighlightStyle()
    {
        return [
            'pre_tags' => $this->highlightPreTags,
            'post_tags' => $this->highlightPostTags
        ];
    }

    public function setHighlight(array $keys)
    {
        if ($keys) {
            $fields = [];
            foreach ($keys as $key) {
                $fields[$key] = $this->defaultHighlightStyle();
            }
            $this->query_param['body']['highlight'] = [
                "fields" => $fields
            ];
        }
    }


    /**
     * @param array $param
     */
    public function setFilter(array $param)
    {
        if ($param) {
            foreach ($param as $key => $value) {
                $filters[]['term'][$key] = $value;
            }
            if (!empty($filters)) {
                $this->query_param['body']['query']['bool']['filter']['bool']['must'] = $filters;
            }
        }
    }

    /**
     * @param array $order
     */
    public function setSort($order = array())
    {
        if (is_array($order)) {
            $this->query_param['body']['sort'] = $order;
        }

    }

    /**
     * @param array $param
     */
    public function setQuery(array $param)
    {
        if ($param) {
            $this->query_param['body']['query'] = $param;
        }
    }

    public function getAggregations($key, $list = array())
    {
        $aggregations = !empty($list) ? $list : $this->aggregations;
        return !empty($aggregations[$key]['buckets']) ? $aggregations[$key]['buckets'] : array();
    }


    public function _part_no($str)
    {
        return strtolower(preg_replace("/[^A-Za-z0-9]/s", "", trim($str)));
    }


    public function getHighlight($id, $key = null)
    {
        $res = [];
        if (!empty($this->highlightList[$id])) {
            $res = $this->highlightList[$id];
        }
        if ($res && $key) {
            return !empty($res[$key]) ? $res[$key] : array();
        }
        return $res;
    }

    public function setAggs($aggs)
    {
        if ($aggs){
            $this->query_param['body']['aggs'] = $aggs;
        }
    }


    public function getCond($str,$default=null){
        return Common::arrayGet($this->condition,$str,$default);
    }

    public function getSource(){
        return $this->source;
    }

    public function getResultIds(){
        return $this->resultIds;
    }

    public function getResultTotal(){
        return $this->resultTotal;
    }

    public function getResult(){
        return $this->result;
    }

    public function getResultSource(){
        return $this->resultSource;
    }


    protected function updateResultSource($key,$data){
        $this->result[$key]['_source']=$data;
    }

    public function getHighlightList(){
        return $this->highlightList;
    }


}