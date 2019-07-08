<?php

namespace ICSearch\libs;

/**
 * ESQuery
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/3 0003
 * Time: 14:22
 */
class ESQuery
{


    const OPERATOR_AND = 'and';
    const OPERATOR_OR = 'or';
    const OPERATOR_MUST = 'must';
    const OPERATOR_SHOULD = 'should';


    static function addBoolQuery(&$param, $newParam, $bool = self::OPERATOR_SHOULD, $minimumShouldMatch = null)
    {
        if ($newParam && is_array($newParam)) {
            self::addListQuery($param, self::boolQuery($newParam, $bool, $minimumShouldMatch));
        }
    }


    static function boolQuery($newParam, $bool = self::OPERATOR_SHOULD, $minimumShouldMatch = null)
    {
        $res = [];
        if ($newParam &&  $bool) {
            $res = [
                "bool" => [
                    $bool => $newParam
                ]
            ];
            if ($bool == self::OPERATOR_SHOULD && $minimumShouldMatch) {
                $res['bool']['minimum_should_match'] = $minimumShouldMatch;
            }
        }
        return $res;
    }


    static function addListQuery(&$param, $newParam)
    {
        if ($newParam && is_array($newParam)) {
            $param = is_array($param) ? $param : array();
            $param[] = $newParam;
        }
    }

    static function addMatchQuery(&$param, $key, $keyword, $operator = self::OPERATOR_AND)
    {
        if ($key && $keyword) {
            $param = is_array($param) ? $param : array();
            $param[] = self::matchQuery($key, $keyword, $operator);
        }
    }

    static function matchQuery($key, $keyword, $operator = self::OPERATOR_AND)
    {
        if ($keyword && $key) {
            return [
                "match" => [
                    $key => [
                        "query" => $keyword,
                        "operator" => $operator,
                    ]
                ],
            ];
        }
    }


    static function termsQuery($key, $list, $boost = null)
    {
        $res = [];
        if (is_array($list) && $list && $key) {
            $res = [
                'terms' => [
                    $key => $list,
                ],
            ];
            if (!is_null($boost)) {
                $res['terms']['boost'] = self::formatBoost($boost);
            }
        }
        return $res;
    }

    static function termQuery($key, $str, $boost = null)
    {
        $res = [];
        if ($key) {
            if ($boost) {
                $res = [
                    'term' => [
                        $key => [
                            "term" => $str,
                            'boost' => self::formatBoost($boost)
                        ]
                    ],
                ];
            } else {
                $res = [
                    'term' => [
                        $key => $str
                    ],
                ];
            }
        }
        return $res;
    }


    static function constantScore($query, $boost = 1)
    {
        $res = [];
        if ($query) {
            $res = [
                "constant_score" => [
                    "query" => $query,
                    'boost' => self::formatBoost($boost)
                ],
            ];
        }
        return $res;
    }

    static function formatBoost($boost)
    {
        return floatval($boost);
    }
}
