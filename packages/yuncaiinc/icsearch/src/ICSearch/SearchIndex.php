<?php namespace ICSearch;

use Elasticsearch\ClientBuilder;
use ICSearch\libs\Common;

class SearchIndex
{

    public $client = null;
    public $indexName = null;
    public $indexType = null;

    public $config = array();

    protected $hosts = [];


    /**
     * @param $connections
     * @throws \Exception
     */
    public function initSearch($connections)
    {
        if ($connections) {
            $this->client = ClientBuilder::create()->setHosts($connections['hosts'])->build();
            $this->indexName = $connections['index'];
            $this->indexType = $connections['type'];
        } else {
            throw new \Exception("初始化搜索失败");
        }
    }


    public function getMapping()
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->indexType,
        ];
        $ret = $this->client->indices()->getMapping($params);
        return $ret;
    }


    public function putMapping()
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->indexType,
        ];
        $ret = $this->client->indices()->putMapping($params);
        return $ret;
    }


    public function delIndex()
    {
        $param = ['index' => $this->indexName];
        $res = $this->client->indices()->exists($param);
        if ($res) {
            $response = $this->client->indices()->delete($param);
            return $response;
        }
    }

    public function getDocument($id)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->indexType,
            'id' => $id
        ];
        $response = $this->client->get($params);
        return $response;
    }


    public function getIndex()
    {
        $params = [
            'index' => $this->indexName
        ];
        $res = $this->client->indices()->exists($params);
        if ($res) {
            $response = $this->client->indices()->get($params);
            return $response;
        }
    }


    /**
     * @param $keyword
     * @param string $analyzer ik_max_word ik_smart
     * @return array
     */
    public function analyze($keyword, $analyzer = 'ik_smart')
    {
        $param['index'] = $this->indexName;
//        $param['type'] = $this->indexType;
        $param['analyzer'] = $analyzer;//
        $param['text'] = $keyword;
        $ret = $this->client->indices()->analyze($param);
        return $ret;
    }

    public function hasIndex()
    {
        $param = ['index' => $this->indexName];
        return $this->client->indices()->exists($param);

    }

    public function _bulk($data)
    {
        return $this->client->bulk($data);
    }


    public function _bulkAction($action, $id)
    {
        $data = [
            $action => [
                '_id' => $id,
                '_index' => $this->indexName,
                '_type' => $this->indexType
            ]
        ];
        return $data;
    }

    public function _bulkBody($action, array $body)
    {

        switch ($action) {
            case 'update':
                $body = [
                    "doc" => $body
                ];
                break;
        }
        return $body;
    }

    public function _bulkHeader()
    {
        $index_data = [
            'index' => $this->indexName,
            'type' => $this->indexType,
        ];
        return $index_data;
    }

    public function createIndex($params)
    {
        return $this->client->indices()->create($params);
    }

    public function _part_no($str)
    {
        return Common::partNo($str);
    }


    public function _configSetting()
    {
        $settings = [
            'number_of_shards' => 1, //被破坏的相关度
            'number_of_replicas' => 0,//关闭副本，线上是为1
//                        'refresh_interval'=>-1,//关掉刷新
            'refresh_interval' => '60s',//60s秒后刷新
            'analysis' => [
                'char_filter' => $this->_charFilter(),
                'analyzer' => $this->_analyzer(),
                'tokenizer' => $this->_tokenizer(),
                'filter' => $this->_filter(),
            ],
        ];
        return $settings;
    }

    public function _charFilter()
    {
        return [
            "&_to_and" => [
                "type" => "mapping",
                "mappings" => [
                    "&=> and "
                ]
            ],
            'spec_unit' => [
                "type" => "mapping",
                "mappings" => [
                    "%=>per",
                    "°=>deg",
                    "µ=>u",
                    "Ω=>ohms",
                    "℃=>cel",
                    "ppm/℃=>ppm",
                    "μ=>u",
                ]
            ],
            'part_no' => [
                'type' => 'pattern_replace',
                'pattern' => "([^A-Za-z0-9])",
                "replacement" => ""
            ],
        ];
    }


    public function _analyzer()
    {
        $res = [
            'part_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'fi_ngram_tokenizer',
                'filter' => [
                    "lowercase",
                ],
                "char_filter" => ["html_strip", "part_no"]
            ],
            'part_extra_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'tokenizer_extra_part',
                'filter' => [
                    "lowercase",
                ],
                "char_filter" => ["html_strip", "part_no"]
            ],
            'part_full_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'keyword',
                'filter' => [
                    "lowercase",
                ],
                "char_filter" => ["html_strip", "part_no"]
            ],
            'mfr_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'ik_smart',
                'filter' => [
                    "lowercase",
                    "mfr_synonym",
                ],
                "char_filter" => ["html_strip", "&_to_and"]
            ],
            'search_mfr_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'ik_smart',
                'filter' => [
                    "lowercase",
                ],
                "char_filter" => ["html_strip", "&_to_and"]
            ],
            'cate_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'ik_smart',
                'filter' => [
                    "lowercase",
                    "cate_synonym",
                ],
                "char_filter" => ["html_strip"]
            ],
            'search_cate_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'ik_smart',
                'filter' => [
                    "lowercase",
                ],
                "char_filter" => ["html_strip"]
            ],
            'desc_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'ik_smart',
                'filter' => $this->_descFilter(),
                "char_filter" => ["html_strip", "spec_unit", "&_to_and"]
            ],
            'search_desc_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'ik_smart',
                'filter' => $this->_searchDescFilter(),
                "char_filter" => ["html_strip", "spec_unit", "&_to_and"]
            ],
            'desc_en_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'classic',
                'filter' => $this->_descFilter(),
                "char_filter" => ["html_strip", "spec_unit", "&_to_and"]
            ],
            'search_desc_en_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'classic',
                'filter' => $this->_searchDescFilter(),
                "char_filter" => ["html_strip", "spec_unit", "&_to_and"]
            ],
            'fi_ik_max_word_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'ik_max_word',
                'filter' => [
                    "lowercase",
                ],
                "char_filter" => ["html_strip", "spec_unit"]
            ],
            'fi_ik_smart_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'ik_smart',
                'filter' => [
                    "lowercase",
                ],
                "char_filter" => ["html_strip", "spec_unit"]
            ],
            'fi_english_analyzer' => [
                'tokenizer' => 'classic',
                'filter' => [
                    "lowercase",
                    "cate_synonym",
                    "mfr_synonym",
                ],
                "char_filter" => ["html_strip", "spec_unit"]
            ],
            'fi_search_english_analyzer' => [
                'tokenizer' => 'classic',
                'filter' => [
                    "lowercase",
                ],
                "char_filter" => ["html_strip", "spec_unit"]
            ],
            'spec_analyzer' => [
                'tokenizer' => 'whitespace',
                'filter' => $this->_searchDescFilter(),
                "char_filter" => ["html_strip", "spec_unit"]
            ],
            'search_spec_analyzer' => [
                'tokenizer' => 'whitespace',
                'filter' => $this->_searchDescFilter(),
                "char_filter" => ["html_strip", "spec_unit"]
            ],
            'keyword_analyzer' => [
                "type" => "custom",
                'tokenizer' => 'ik_smart',
                'filter' => [
                    "lowercase",
                    "cate_synonym",
                    "mfr_synonym"
                ],
                "char_filter" => ["html_strip"]
            ],
           'test_analyzer'=>[
               'tokenizer'=>'ik_smart',
               'filter'=> [
                   "lowercase",
                   "test_synonym",
               ],
               "char_filter" => ["html_strip", "spec_unit"]
           ]
        ];
        return $res;
    }

    public function _descFilter()
    {
        $list = $this->_searchDescFilter();
        array_push($list, 'mfr_synonym', 'cate_synonym');
        return $list;
    }

    public function _searchDescFilter()
    {
        $list = $this->_uniteFilterList();
        array_unshift($list, 'lowercase');
        return $list;
    }

    public function _uniteFilterList()
    {
        return array_keys($this->_uniteFilter());
    }


    public function _tokenizer()
    {
        return [
            'fi_ngram_tokenizer' => [
                'type' => 'ngram',
                'min_gram' => 3,
                'max_gram' => 3,
            ],
            'tokenizer_extra_part' => [
                'type' => 'edgeNGram',
                'min_gram' => 2,
                'max_gram' => 50,
            ],
        ];
    }

    public function _filter()
    {
        $synonym = [
            'unit_synonym' => [
                'type' => 'synonym',
                'synonyms_path' => 'synonyms/unit.txt',
            ],
            'mfr_synonym' => [
                'type' => 'synonym',
                'synonyms_path' => 'synonyms/mfr.txt',
            ],
            'cate_synonym' => [
                'type' => 'synonym',
                'synonyms_path' => 'synonyms/cate.txt',
            ],
            'number_pack_synonym' => [
                'type' => 'synonym',
                'synonyms_path' => 'synonyms/numberPack.txt',
            ],
            'other_synonym' => [
                'type' => 'synonym',
                'synonyms_path' => 'synonyms/other.txt',
            ],
            'test_synonym'=>[
                'type' => 'synonym',
                'synonyms' => [
                    "a&d instruments=>a&d",
                    "a.w. sperry=>aw sperry",
                    "思瑞浦=>3peak",
                    "bt(british telecom)=>british telecom",
                ],
            ]
        ];
        $unit = $this->_uniteFilter();
        return array_merge($synonym, $unit);
    }

    public function _unitDict()
    {
        return [
            '﻿deg,度',
            'a,安,安培,amp,amps',
            'adc,adcs',
            'bit,bits,位',
            'byte,字节,位元',
            'channels,通道',
            'circuits,电路',
            'cm,厘米',
            'f,法,法拉',
            'ft,英尺,feet,foot',
            'g,克',
            'gates,门',
            'ghz,吉赫',
            'gohms,gohm,吉欧',
            'h,亨',
            'hz,赫兹',
            'in,inch,英寸',
            'ka,千安',
            'kb,kbyte',
            'kbit,kbits',
            'kg,千克,公斤',
            'khz,k赫兹,千赫',
            'km,千米,公里',
            'kohm,kohms,千欧',
            'kv,千伏,kvolts',
            'kw,千瓦',
            'kwh,千瓦时',
            'ma,毫安,maout,main',
            'mf,毫法',
            'mh,毫亨',
            'mhz,兆赫',
            'mm,毫米',
            'mohm,兆欧,mohms',
            'mv,毫伏',
            'mw,毫瓦',
            'nf,纳法',
            'nh,纳亨',
            'ns,纳秒',
            'ohm,ohms,r,欧姆,欧',
            'pf,皮法',
            'pin,pins,脚,引脚',
            'rows,排',
            'tohms,tohm,太欧',
            'ua,微安',
            'uart,uarts',
            'uf,微法',
            'uh,微亨',
            'uohm,uohms,微欧',
            'us,微秒',
            'uv,微伏',
            'uw,微瓦',
            'v,伏,伏特,vin,vout,vac,vdc,volt,volts',
            'va,伏安',
            'w,watts,watt,瓦,瓦特',
            'position,位置,pos,positions',
            'contact,针,芯,contacts,触点,触头',
            'cond,conductors,conductor',
            'ah,安时',
            'mmsq,mm2,sqmm',
        ];
    }

    public function _uniteFilter()
    {
        $res = [];
        $unitDict = $this->_unitDict();
        if ($unitDict) {
            foreach ($unitDict as $v) {
                $firstUnit = strstr($v, ',', true);
                $otherUnit = ltrim(strstr($v, ','), ',');
                if ($firstUnit && $otherUnit) {
                    $res[$firstUnit . '_unit'] = [
                        'type' => 'pattern_replace',
                        'pattern' => '^([0-9.]+)(' . str_replace(",", "|", $otherUnit) . '){1}$',
                        "replacement" => "$1" . $firstUnit
                    ];
                }

            }
        }
        return $res;
    }

    public function setAlias($name)
    {
        $params = [
            'index' => $this->indexName,
            'name' => $name
        ];
        $response = $this->client->indices()->putAlias($params);
        Common::dd($response);
    }

    public function getAlias($name)
    {
        $params = [
            'index' => $this->indexName,
            'name' => $name
        ];
        $response = $this->client->indices()->getAlias($params);
        return $response;
    }

    public function delAlias($name)
    {
        $params = [
            'index' => $this->indexName,
            'name' => $name
        ];
        $response = $this->client->indices()->deleteAlias($params);
        return $response;
    }

    public function open()
    {
        $params = [
            'index' => $this->indexName,
        ];
        $response = $this->client->indices()->open($params);
        return $response;
    }


    public function close()
    {
        $params = [
            'index' => $this->indexName,
        ];
        $response = $this->client->indices()->close($params);
        return $response;
    }

    public function aliases($params)
    {
        $response = array();
        if (!empty($params)) {
            $response = $this->client->indices()->updateAliases($params);
        }
        return $response;
    }

    public function dynamicSetting()
    {
        $this->close();
        $params = [
            'index' => $this->indexName,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'char_filter' => $this->_charFilter(),
                        'analyzer' => $this->_analyzer(),
                        'tokenizer' => $this->_tokenizer(),
                        'filter' => $this->_filter(),
                    ],
                ],
            ],
        ];
        $response = $this->client->indices()->putSettings($params);
        $this->open();
        return $response;
    }


    /**
     * @param array $ids
     * @return array
     */
    public function bulkDelDoc(array $ids)
    {
        $index_data = $this->_bulkHeader();
        foreach ($ids as $id) {
            $index_data['body'][] = $this->_bulkAction('delete', $id);
        }
        $response = $this->_bulk($index_data);
        return $response;
    }


}
