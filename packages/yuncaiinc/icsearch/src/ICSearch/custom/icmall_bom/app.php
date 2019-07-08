<?php
/**
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/4 0004
 * Time: 11:13
 */

return [
    'fields'=>[
        'ID' => [
            'highlight'=>false,
            'field' => '_id'
        ],
        'part'=>[
            'highlight'=>true,
            'field'=>'part_no'
        ],
        'partExtra'=>[
            'highlight'=>true,
            'field'=>'part_no.extra'
        ],
        'partFull'=>[
            'highlight'=>true,
            'field'=>'part_no.full',
        ],
        'cateID'=>[
            'highlight'=>false,
            'field'=>'cate_id',
        ],
        'cate'=>[
            'fields' => [
                'cn'=>[
                    'highlight'=>true,
                    'field' => 'cate',
                ]
            ],
        ],
        'spec'=>[
            'highlight'=>true,
            'field'=>'spec',
        ],
        'mfrID'=>[
            'field'=>'brand_id',
        ],
        'mfr'=>[
            'fields' => [
                'cn'=>[
                    'highlight'=>true,
                    'field' => 'brand_name',
                ],
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
        'extraVerify'=>[
            'field'=>'extra_verify',
        ],
        'sku'=>[
            'field'=>'sku_info',
        ],
    ],
    'expandFields'=>[
        'keyword'=>[
            [
                'highlight'=>true,
                'field'=>'extra_spec',
            ],
        ]
    ],
    'version'=>'v1',
    'mfrIdFileKey'=>'icmall'

];