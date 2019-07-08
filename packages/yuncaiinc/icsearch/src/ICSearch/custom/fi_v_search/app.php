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
                    'field' => 'cate',
                ]
            ],
        ],
        'spec' => [
            'field' => 'spec',
        ],
        'mfrID' => [
            'field' => 'mfr_id',
        ],
        'mfr'=>[
            'fields' => [
                'cn'=>[
                    'highlight'=>true,
                    'field' => 'show_mfr',
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
        'extraSpec' => [
            'field' => 'extra_spec',
        ],
    ],
    'expandFields'=>[
        'keyword'=>[
            [
                'highlight'=>true,
                'field'=>'vendor_name'
            ],
            [
                'highlight'=>true,
                'field'=>'case_package'
            ]
        ]
    ],
    'version'=>'v1',
    'mfrIdFileKey'=>'findic'

];