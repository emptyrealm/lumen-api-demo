<?php
/**
 * Created by PhpStorm.
 * User: lance
 * Date: 2018/12/14 0014
 * Time: 10:55
 */

[
    'mappings'=>[
        'properties'=>[
            'part_no'=>[
                'type'=>'text',
                'analyzer'=>'part_analyzer',
                "index_options"=>"offsets",
                "fields" => [
                    'extra' => [
                        'type' => 'text',
                        'analyzer' => 'part_extra_analyzer',
                        "index_options" => "docs",
                    ],
                    'full' => [
                        'type' => 'text',
                        'analyzer' => 'part_full_analyzer',
                    ]
                ],
            ],
            'spec'=>[
                'type'=>'text',
                'analyzer'=>'spec_analyzer',
                "norms"=>[
                    "enabled"=>false
                ],
                "index_options" => 'docs'
            ],
            'mfr_cn'=>[
                'type'=>'text',
                'analyzer'=>'mfr_analyzer',
                "index_options"=>'docs',
            ],
            'mfr_en'=>[
                'type'=>'text',
                'analyzer'=>'mfr_analyzer',
                "index_options"=>'docs',
            ],
            'desc_cn'=>[
                'type'=>'text',
                'analyzer'=>'desc_analyzer',
                "index_options"=>'docs',
                "norms"=>[
                    "enabled"=>false
                ],
            ],
            'desc_en'=>[
                'type'=>'text',
                'analyzer'=>'desc_analyzer',
                "index_options"=>'docs',
                "norms"=>[
                    "enabled"=>false
                ],
            ],
            'mfr_id'=>[
                'type'=>'integer',
                'index'=>'not_analyzed',
            ],
            'category_id'=>[
                'type'=>'integer',
                'index'=>'not_analyzed',
            ],
            'extra_spec'=>[
                'type'=>'text',
                'analyzer'=>'spec_analyzer',
                "norms"=>[
                    "enabled"=>false
                ],
                "index_options"=>'docs'
            ],
            'cate_cn'=>[
                'type'=>'text',
                'analyzer'=>'cate_analyzer',
                "index_options"=>'docs',
            ],
            'cate_en'=>[
                'type'=>'text',
                'analyzer'=>'cate_analyzer',
                "index_options"=>'docs',
            ],
            'extra_verify' => [
                'type' => 'boolean',
                'index' => true,
            ]
        ]
    ]
];