<?php

namespace app\common\controller;

use think\Controller;
use think\Request;
use OSS\Core\OssException;
use OSS\OssClient;
use think\facade\Config;
use think\Image;

// 引入异常类
use app\lib\exception\BaseException;

class OSSController
{
    // 上传文件 
    static public function uploadFile($file)
    {
        // $file = request()->file('file');  //获取到上传的文件
        $resResult = Image::open($file);
        // 尝试执行
        try {
            // $config = Config::pull('aliyun_oss'); //获取Oss的配置
            //实例化对象 将配置传入
            $ossClient = new OssClient(config('api.aliOSS.KeyId'), config('api.aliOSS.KeySecret') , config('api.aliOSS.Endpoint'));
            //这里是有sha1加密 生成文件名 之后连接上后缀
            $fileName = sha1(date('YmdHis', time()) . uniqid()) . '.' . $resResult->type();
            //执行阿里云上传
            $result = $ossClient->uploadFile(config('api.aliOSS.Bucket'), 'appsource/hpubox/'.date('Y-m-d').'/'.$fileName, $file->getInfo()['tmp_name'] );
            /**
             * 这个只是为了展示
             * 可以删除或者保留下做后面的操作
             */
            $arr = [
                'url' => $result['info']['url'],
                'filename' => $fileName
            ];
        } catch (OssException $e) {
            return $e->getMessage();
        }
        //将结果输出
        // dump($arr);
        return $arr;
    }
    
}
