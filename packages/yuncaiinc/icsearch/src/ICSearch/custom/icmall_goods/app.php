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
            'field'=>'goods_name'
        ],
        'partExtra'=>[
            'highlight'=>true,
            'field'=>'goods_name.extra'
        ],
        'partFull'=>[
            'highlight'=>true,
            'field'=>'goods_name.full',
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
        ]
    ],
    'expandFields'=>[
        'keyword'=>[
            [
                'highlight'=>true,
                'field'=>'extra_spec',
            ],
            [
                'highlight'=>true,
                'field'=>'goods_body',
            ],
        ]
    ],
    'version'=>'v1',
    'mfrIdFileKey'=>'icmall'

];