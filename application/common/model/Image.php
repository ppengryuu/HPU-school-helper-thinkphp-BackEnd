<?php

namespace app\common\model;

use think\Model;
use app\lib\exception\BaseException; 
use app\common\controller\OSSController;

class Image extends Model
{
    // 自动写入时间
    protected $autoWriteTimestamp = true;

    // 上传多图
    public function uploadMore(){
        $image = $this->upload(request()->userId,'imglist');
        for ($i=0; $i < count($image); $i++) { 
            $image[$i]['url'] = getFileUrl($image[$i]['url']);
        }
        return $image;
    }

  
  public function getUrlAttr($value){
        if (strpos($value,'http') === false) {
            // 不包含
            $value = getFileUrl($value);
        }
        return $value;
    }
  
  
    // 上传图片
    public function upload($userid = '',$field = ''){
        // 获取图片
        $files = request()->file($field);
        if (is_array($files)) {
            // 多图上传
            foreach($files as $file){
                $res = OSSController::uploadFile($file);
                return self::create([
                    'url'=>$res['url'],
                    'filename'=>$res['filename'],
                    'user_id'=>$userid
                ]);
                
            }
        }
        // 单图上传
        if(!$files) TApiException('请选择要上传的图片',10000,200);
        // 单文件上传
        // print_r($files);
        $res = OSSController::uploadFile($files);
        // print_r($res);
        return self::create([
            'url'=>$res['url'],
            'filename'=>$res['filename'],
            'user_id'=>$userid
        ]);
    }


    // 图片是否存在
    public function isImageExist($id,$userid){
        return $this->where('user_id',$userid)->find($id);
    }

}
