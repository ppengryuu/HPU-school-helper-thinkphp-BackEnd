<?php

namespace app\common\validate;

use think\Validate;

class UserValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'jwzh'=>'require',
        'jwmm'=>'require',
        'usernum'=>'require',
        'phone'=>'require|mobile',
        'phonecode'=>'require|number|isPefectCodeP',
        'emailcode'=>'require|number|isPefectCodeE',
        'username'=>'require',
        'password'=>'require|alphaDash',
        'provider'=>'require',
        'openid'=>'require',
        'nickName'=>'require',
        'avatarUrl'=>'require',
        'expires_in'=>'require',
        'id'=>'require|integer|>:0',
        'page'=>'require|integer|>:0',
        'email'=>'require|email',
        'userpic'=>'image',
        'userbgpic'=>'image',
        'name'=>'require|chsDash',
        'sex'=>'require|in:0,1,2',
        'qg'=>'require|in:0,1,2',
        'job'=>'require|chsAlpha',
        'birthday'=>'require|dateFormat:Y-m-d',
        'path'=>'require|chsDash',
        'oldpassword'=>'require',
        'newpassword'=>'require|alphaDash',
        'renewpassword'=>'require|confirm:newpassword',
        'follow_id'=>'require|integer|>:0|isUserExist',
        'user_id'=>'require|integer|>:0',
        'cardnum'=>'require|cardExist',
        'url'=>'require',
        'type'=>'require',
        'coursename'=>'require',
        'semester'=>'require',
        'semster'=>'require',
        'postid'=>'require|isPostOwner',
        'commentid'=>'require|isCommentOwner',
        'week'=>'require|>:0|<:8',
        'campus'=>'require|isCampusExist',
        'building'=>'require|isBuildingExist',
        'schoolww'=>'require',
        'course'=>'require',
        'section'=>'require',
        'length'=>'require',
        'active'=>'require',
        'oldemail'=>'require',
        'captcha'=>'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'emailcode.number'=>'验证码应为数字',
        'phonecode.number'=>'验证码应为数字',
        'birthday.dateFormat:Y-m-d'=>'生日格式不正确',
        'phone.require'=>'请填写手机号码',
        'phone.mobile'=>'手机号码不合法'
    ];

    // 配置场景
    protected $scene = [
        // 修改课程表
        'edittimetable'=>['course','weekday','sections','length','type'],
        // 查询空教室
        'getemptyclassroom'=>['week','campus','building','schoolww'],
        // 举报用户、文章、评论 % 0 1 2(:type)
        'report' => ['id'],
        // 删除评论
        'delmycomment' =>['commentid'],
        // 删除动态
        'delmypost' =>['postid'],
        // 科目排名
        'getcourseranking'=>['coursename','semester'],
        // 取前100名 传入type 获取不同类型
        'gettopranking'=>['type','semster'],
        // 解析图书馆图书页面
        'parsepage'=>['url'],
        // 发布校园卡寻找失主
        'findCard'=>['cardnum','contactway','selfpick','describe'],
        // 绑定教务
        'bindEAS'=>['jwzh','jwmm','captcha'],
        // 登录图书馆
        'libLogin'=>['usernum','password', 'captcha'],
        // 发送验证码
        'sendCode'=>['phone'],
        // 发送邮箱验证码
        'sendEmailCode'=>['email'],
        // 手机号登录
        'phonelogin'=>['phone','phonecode'],
        // 邮箱登录
        'emaillogin'=>['email','emailcode'],
        // 账号密码登录
        'login'=>['usernum','password'],
        // 第三方登录
        'otherlogin'=>['provider','openid','nickName','avatarUrl','expires_in'],
        'post'=>['id','page'],
        'allpost'=>['page'],
        'bindphone'=>['phone','code'],
        // $$
        'changebindphone'=>['phone','phonecode'],
        'changebindemail'=>['oldemail','email','emailcode'],
        // #$
        'bindemail'=>['email'],
        'bindother'=>['provider','openid','nickName','avatarUrl'],
        'edituserpic'=>['userpic'],
        'edituserbgpic'=>['userbgpic'],
        'edituserinfo'=>['name','sex','qg','job','birthday','path'],
        'repassword'=>['token','newpassword','renewpassword'],
        'follow'=>['follow_id'],
        'unfollow'=>['follow_id'],
        'getfriends'=>['page'],
        'getfens'=>['page'],
        'getfollows'=>['page'],
    	'getuserinfo'=>['user_id']
    ];


}
