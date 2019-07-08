<?php
/**
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/4 0004
 * Time: 11:13
 */

return [
    'fields' => [
        'ID' => [
            'highlight'=>false,
            'field' => '_id'
        ],
        'part' => [
            'highlight'=>true,
            'field' => 'part_no'
        ],
        'partExtra' => [
            'highlight'=>true,
            'field' => 'part_no.extra'
        ],
        'partFull' => [
            'highlight'=>true,
            'field' => 'part_no.full',
        ],
        'cateID' => [
            'highlight'=>false,
            'field' => 'category_id',
        ],
        'cate'=>[
            'fields' => [
                'cn'=>[
                    'highlight'=>true,
                    'field' => 'cate_cn',
                ],
                'en'=>[
                    'highlight'=>true,
                    'field' => 'cate_cn',
                ]
            ],
        ],
        'spec' => [
            'highlight'=>true,
            'field' => 'spec',
        ],
        'mfrID' => [
            'field' => 'mfr_id',
        ],
        'mfr'=>[
            'fields' => [
                'cn'=>[
                    'highlight'=>true,
                    'field' => 'mfr_cn',
                ],
                'en'=>[
                    'highlight'=>true,
                    'field' => 'mfr_en',
                ]
            ],
        ],
        'desc'=>[
            'fields' => [
                'cn'=>[
                    'highlight'=>true,
                    'field' => 'desc_cn',
                ],
                'en'=>[
                    'highlight'=>true,
                    'field' => 'desc_en',
                ]
            ],
        ],
        'extraVerify' => [
            'field' => 'extra_verify',
        ],
    ],
    'connections' => [
        'hosts' => [
            [
                'host' => '120.76.46.169',
                'port' => '9200',
                'scheme' => 'http',
                'user' => 'elastic',
                'pass' => 'vQvG6r6LA',
            ]
        ],
        'index' => 'fi_test',
        'type' => 'fi_column',
    ],
    'setting'=>[
        'shouldMinimumShouldMatch'=>3,
        'boost'=>[
            'default'=>1,
            'numberPack'=>3,
            'letterPack'=>2,
            'LetterPack'=>3,
            'spec'=>2.5,
            'material'=>2,
            'cate'=>1.5,
            'part'=>5,
            'mfr'=>2
        ],
    ],
    'expandFields'=>[
        'keyword'=>[
            [
                'field'=>'extra_spec'
            ]
        ]
    ],
    'version'=>'v1'

];