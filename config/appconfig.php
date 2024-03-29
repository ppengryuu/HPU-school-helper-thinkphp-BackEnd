<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用名称
    // 'app_name'               => 'Hpu盒子',

    
    // 协议政策版本, 与其它版本独立
    'eusa_version'=>5,
    // 最新软件版本及下载地址, 与其它版本独立
    'app_version'=>'1.3.4',
    // 'app_url'=>'https://www.coolapk.com/apk/258478',

    // 配置版本  更改以同步下列配置到用户 与其他版本独立
    'version'=>19,
    // 当前学期
    'semester'=>[
        "id" => "64",
        "name" => "2021-2022 1",
        "firstDay" => "2021/8/30"
    ],
    // 'semester'=>[
    //     "id" => "63",
    //     "name" => "2020-2021 2",
    //     "firstDay" => "2021/2/28"
    // ],
    // 学期列表
    
    // 课程表时间
    // 春季作息
    'course_on_time'=>['8:00','9:00','10:10','11:10','14:30','15:30','16:30','17:30','19:00','20:00'],
    'course_over_time'=>['8:50','9:50','11:00','12:00','15:20','16:20','17:20','18:20','19:50','20:50'],

    // 夏季作息
    'course_on_time'=>['8:00','9:00','10:10','11:10','15:00','16:00','17:00','18:00','19:30','20:30'],
    'course_over_time'=>['8:50','9:50','11:00','12:00','15:50','16:50','17:50','18:50','20:20','21:20'],
    // 校区
    // 'campus'=>['南校区','北校区'
    // ,'建设路校区'
    // ],
    // 教学楼
    // 'campus_1'=>['1号教学楼','2号教学楼','3号教学楼','计算机综合楼','经管综合楼','文科综合楼','材料综合楼','机械综合楼',
    // '电气综合楼','能源综合楼','资环综合楼','土木综合楼','测绘综合楼',
    // '语音室','设计专教','音乐系','体育系','实践教学','工程训练中心','东区体育场','西区体育场','西区室内场地','1号实验楼',
    // '东区室内场地','体育馆','综合实验大楼','东区体育场地','西区体育场地','尔雅楼','活动中心',
    // '理化综合楼'],
    // 'campus_2'=>['北校区1号教学楼','北校区2号教学楼'
    // ,'北区体育场'
    // ],
    // 'campus_3'=>['教学楼','电教楼'
    // ,'体育场地'
    // ],
    // 学校周校准 旧版支持
    'schoolww_adjust'=>35,
    // 图片referer
    'img_referer'=>'https://api.hpubox.top/',

    'json_url'=>'https://api.hpubox.top/sources/sider',
    
];
