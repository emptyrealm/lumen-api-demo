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
                    'field' => 'cate_en',
                ],
                'expand'=>[
                    'highlight'=>true,
                    'field' => 'cate_expand',
                ],
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
    'expandFields'=>[
        'keyword'=>[
            [
                'highlight'=>true,
                'field'=>'extra_spec'
            ]
        ]
    ],
    'version'=>'v1',
    'mfrIdFileKey'=>'findic'

];