<?php

namespace app\common\validate;

use think\Validate;

class MessageValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
    protected $rule = [
        'to_id'=>'require|isUserExist',
        'from_userpic'=>'require',
        'type'=>'require',
        'data'=>'require',
     	 'client_id'=>'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	

    // from_userpic
    // from_username
    // from_post
    // from_postid
    protected $message = [];

    protected $scene = [
        'send'=>['to_id','from_userpic','type','data'],
      	'bind'=>['type','client_id']
    ];
}
