<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Request;
use app\common\controller\BaseController;
use app\lib\exception\BaseException;
use app\common\validate\UserValidate;
use app\common\model\User as UserModel;

class User extends BaseController
{

    // 用户修改院校
    public function schoolSet(){
        $data = (new UserModel())->schoolSet();
        return self::showResCode('修改成功',$data);
    }

    // 同步微信资料
    public function syncWx(){
        // 同步微信资料
        $data = (new UserModel())->syncWx();
        //print_r($data);
        return self::showResCode('修改成功',$data);
    }

    // 修改课程表
    public function editTimeTable(){
        // 修改课程表
        (new UserValidate())->goCheck('edittimetable');
        $data = (new UserModel())->editTimeTable();
        //print_r($data);
        return self::showResCode('修改成功',$data);
    }

    // 获取前100名 传入type 获取不同类型
    public function getEmptyClassroom(){
        // 获取前100名 传入type 获取不同类型
        (new UserValidate())->goCheck('getemptyclassroom');
        $data = (new UserModel())->getEmptyClassroom();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 举报用户、文章、评论
    public function Report(){
        // 举报用户、文章、评论
        // 验证用户操作
        (new UserValidate())->goCheck('report');
        $data = (new UserModel())->Report();
        //print_r($data);
        return self::showResCode('举报成功',$data);
    }

    // 删除我的评论
    public function delMyComment(){
        // 验证用户操作
        (new UserValidate())->goCheck('delmycomment');
        $data = (new UserModel())->delMyComment();
        //print_r($data);
        return self::showResCode('删除成功',$data);
    }

    // 删除我的动态
    public function delMyPost(){
        // 验证用户操作
        (new UserValidate())->goCheck('delmypost');
        $data = (new UserModel())->delMyPost();
        //print_r($data);
        return self::showResCode('删除成功',$data);
    }

    // 获取用户评论过的帖子
    public function commentedPost(){
        // 获取用户评论过的帖子
        $list = (new UserModel())->getCommentedPost();
        //print_r($data);
        return self::showResCode('获取成功',['list'=>$list]);
    }

    // 获取用户被回复的动态
    public function beReplyedPost(){
        // 获取用户咱过的文张
        $list = (new UserModel())->getBeReplyedPost();
        //print_r($data);
        return self::showResCode('获取成功',['list'=>$list]);
    }

    // 获取用户咱过的文张
    public function supportedPost(){
        // 获取用户咱过的文张
        $list = (new UserModel())->getSupportedPost();
        //print_r($data);
        return self::showResCode('获取成功',['list'=>$list]);
    }

    // 获取科目排名
    public function getCourseRanking(){
        // 获取科目排名
        (new UserValidate())->goCheck('getcourseranking');
        $data = (new UserModel())->getCourseRanking();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 获取前100名 传入type 获取不同类型
    public function getTopRanking(){
        // 获取前100名 传入type 获取不同类型
        (new UserValidate())->goCheck('gettopranking');
        $data = (new UserModel())->getTopRanking();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 解析图书馆图书页面
    public function ParsePage(){
        // 解析图书馆图书页面
        (new UserValidate())->goCheck('parsepage');
        $data = (new UserModel())->ParsePage();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 获取单日课程表
    public function getDayTimeTable(){
        // 获取单日课程表逻辑
        $data = (new UserModel())->getDayTimeTable();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 获取课程表
    public function getTimeTable(){
        // 获取课程表逻辑
        $data = (new UserModel())->getTimeTable();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 获取成绩
    public function getGrade(){
        // 获取成绩逻辑
        $data = (new UserModel())->getGrade();
        return self::showResCode('获取成功',$data);
    }

    // 获取借阅图书
    public function getBorrowBook(){
        // 获取成绩逻辑
        $data = (new UserModel())->getBorrowBook();
        return self::showResCode('获取成功',$data);
    }

    // 发布校园卡寻找失主
    public function findCard(){
        // 验证参数
        (new UserValidate())->goCheck('findCard');
        // 发布校园卡逻辑
        $data = (new UserModel())->findCard();
        return self::showResCode('发布成功',$data);
    }

    // 绑定图书馆
    public function loanList(){
        // 验证参数
        (new UserValidate())->goCheck('libLogin');
        // 绑定教务逻辑
        $data = (new UserModel())->loanList();
        return self::showResCode('绑定成功',$data);
    }

    // 绑定教务
    public function eamsBind(){
        // 验证参数
        (new UserValidate())->goCheck('bindEAS');
        // 绑定教务逻辑
        $data = (new UserModel())->eamsBind();
        return self::showResCode('绑定成功',$data);
    }

    // 发送邮箱验证码 
    public function sendEmailCode(){
        // 验证参数
        (new UserValidate())->goCheck('sendEmailCode');
        // 发送验证码逻辑
        (new UserModel())->sendEmailCode();
        return self::showResCodeWithOutData('发送成功');
    }

    // 发送手机验证码
    public function sendCode(){
        // 验证参数
        (new UserValidate())->goCheck('sendCode');
        // 发送验证码逻辑
        (new UserModel())->sendCode();
        return self::showResCodeWithOutData('发送成功');
    }

    // 邮箱登录
    public function emailLogin(){
        // 验证参数
        (new UserValidate())->goCheck('emaillogin');
        // 发送验证码逻辑
        $user = (new UserModel())->emailLogin();
        return self::showResCode('登录成功',$user);
    }

    // 微信小程序邮箱登录（不能注册）
    public function wxmpemailLogin(){
        // 验证参数
        (new UserValidate())->goCheck('emaillogin');
        // 发送验证码逻辑
        $user = (new UserModel())->emailLogin(true);
        return self::showResCode('登录成功',$user);
    }

    // 手机号码登录
    public function phoneLogin(){
        // 验证登录信息
        (new UserValidate())->goCheck('phonelogin');
        // 手机登录
        $user = (new UserModel())->phoneLogin();
        return self::showResCode('登录成功',$user);
    }

    // 账号密码登录
    public function login(){
        // 验证登录信息
        (new UserValidate())->goCheck('login');
        // 登录
        $user = (new UserModel())->login();
        return self::showResCode('登录成功',$user);
    }

    // 第三方登录
    public function otherLogin(){
        // 验证登录信息
        (new UserValidate())->goCheck('otherlogin');
        $user =(new UserModel())->otherlogin();
        return self::showResCode('登录成功',$user);
    }

    // 退出登录
    public function logout(){
        (new UserModel())->logout();
        return self::showResCodeWithOutData('退出成功');
    }

    // 用户发布文章列表
    public function post(){
        (new UserValidate())->goCheck('post'); 
        $list = (new UserModel())->getPostList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

    // 用户发布文章列表
    public function Allpost(){
        (new UserValidate())->goCheck('allpost'); 
        $list = (new UserModel())->getAllPostList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

    // 绑定手机
    public function bindphone(){
        (new UserValidate())->goCheck('bindphone');
        $user = (new UserModel())->bindphone();
        return self::showResCode('获取成功',$user);
    }

     // 更换绑定手机 $$
     public function changebindphone(){
        (new UserValidate())->goCheck('changebindphone');
        $user = (new UserModel())->changebindphone();
        return self::showResCode('获取成功',$user);
    }

    // 更换绑定邮箱 $$
    public function changebindemail(){
        (new UserValidate())->goCheck('changebindemail');
        $user = (new UserModel())->changebindemail();
        return self::showResCode('获取成功',$user);
    }

    // 绑定邮箱
    public function bindemail(){
        (new UserValidate())->goCheck('bindemail');
        $data = (new UserModel())->bindemail();
        return self::showResCode('绑定成功', $data);
    }

    // 绑定第三方
    public function bindother(){
        (new UserValidate())->goCheck('bindother');
        (new UserModel())->bindother();
        return self::showResCodeWithOutData('绑定成功');
    }

    // 修改头像
    public function editUserpic(){
        (new UserValidate())->goCheck('edituserpic');      
        $src = (new UserModel())->editUserpic();
        return self::showResCode('修改头像成功',$src);
    }

    // 修改背景
    public function editUserbgpic(){
        (new UserValidate())->goCheck('edituserbgpic');      
        $src = (new UserModel())->editUserbgpic();
        return self::showResCode('修改背景成功',$src);
    }

    // 修改资料
    public function editinfo(){
        (new UserValidate())->goCheck('edituserinfo');
        (new UserModel())->editUserinfo();
        return self::showResCodeWithOutData('修改成功');
    }

    // 修改密码
    public function rePassword(){
        (new UserValidate())->goCheck('repassword'); 
        (new UserModel())->repassword();
        return self::showResCodeWithOutData('修改密码成功');
    }
    
    // 关注
    public function follow(){
        (new UserValidate())->goCheck('follow'); 
        (new UserModel())->ToFollow();
        return self::showResCodeWithOutData('关注成功');
    }

    // 取消关注
    public function unfollow(){
        (new UserValidate())->goCheck('unfollow'); 
        (new UserModel())->ToUnFollow();
        return self::showResCodeWithOutData('取消关注成功');
    }

    // 互关列表
    public function friends(){
        (new UserValidate())->goCheck('getfriends'); 
        $list = (new UserModel())->getFriendsList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

    // 粉丝列表
    public function fens(){
        (new UserValidate())->goCheck('getfens'); 
        $list = (new UserModel())->getFensList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

    // 关注列表
    public function follows(){
        (new UserValidate())->goCheck('getfollows'); 
        $list = (new UserModel())->getFollowsList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

   // 统计获取用户相关数据（总文章数，今日文章数，评论数 ，关注数，粉丝数，文章总点赞数）
    public function getCounts(){
      (new UserValidate())->goCheck('getuserinfo'); 
        $user = (new UserModel())->getCounts();
        return self::showResCode('获取成功',$user);
    }
  
  
  // 判断当前用户userid的第三方登录绑定情况
    public function getUserBind(){
        $user = (new UserModel())->getUserBind();
        return self::showResCode('获取成功',$user);
    }
  
  // 获取用户详细信息
    public function getuserinfo(){
        (new UserValidate())->goCheck('getuserinfo'); 
        $data = (new UserModel())->getUserInfo();
        return self::showResCode('获取成功',$data);
    }
  
  	// 微信小程序登录
  	public function wxLogin(Request $request){
        $url = "https://api.weixin.qq.com/sns/jscode2session";
        // 参数
        $params['appid']= config('api.wx.appid');
        $params['secret']=  config('api.wx.secret');
        $params['js_code']= $request -> param('code');
        $params['grant_type']= 'authorization_code';
        // 微信API返回的session_key 和 openid
        $arr = httpWurl($url, $params, 'POST');
        $arr = json_decode($arr,true);
        // 判断是否成功
        if(isset($arr['errcode']) && !empty($arr['errcode'])){
            return self::showResCodeWithOutData($arr['errmsg']);
        }
        // 拿到数据
        $request->provider = 'weixin';
        $request->openid = $arr['openid'];
      	$request->expires_in = 1000000;
        $user =(new UserModel())->wxmplogin();
        return self::showResCode('登录成功',$user);
    }

    // 微信小程序2 登录（HPU校园助手）
    public function wxLogin2(Request $request){
        $url = "https://api.weixin.qq.com/sns/jscode2session";
        // 参数
        $params['appid']= config('api.wx.appid2');
        $params['secret']=  config('api.wx.secret2');
        $params['js_code']= $request -> param('code');
        $params['grant_type']= 'authorization_code';
        // 微信API返回的session_key 和 openid
        $arr = httpWurl($url, $params, 'POST');
        $arr = json_decode($arr,true);
        // 判断是否成功
        if(isset($arr['errcode']) && !empty($arr['errcode'])){
            return self::showResCodeWithOutData($arr['errmsg']);
        }
        // 拿到数据
        $request->provider = 'weixin';
        $request->openid = $arr['openid'];
      	$request->expires_in = 1000000;
        $user =(new UserModel())->wxmplogin();
        return self::showResCode('登录成功',$user);
    }

  
  	//支付宝小程序登录
    public function alilogin(){
        $code = request()->code;
        include_once(__DIR__.'/../../../../extend/alipaySdk/AopSdk.php');
        //初始化
        $aop = new \AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = config('api.alipay.appid');
        //私钥
        $aop->rsaPrivateKey = config('api.alipay.PrivateKey');
        //公钥
        $aop->alipayrsaPublicKey = config('api.alipay.PublicKey');
        $aop->format = 'json';
        $aop->charset = 'UTF-8';
        $aop->signType = 'RSA2';
        //$aop->apiVersion = '1.0';
        $request = new \AlipaySystemOauthTokenRequest();
        $request->setGrantType("authorization_code");
        $request->setCode($code);
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultData = (array) $result->$responseNode;
        //获取用户信息
        //$request = new \AlipayUserInfoShareRequest ();
        //$result = $aop->execute ($request, $resultData['access_token']);
        //$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        //$userData = (array) $result->$responseNode;
        //halt($userData);//用户公开信息

        // 拿到数据
        $req = request();
        $req->provider = 'alipay';
        $req->openid = $resultData['alipay_user_id'];
      	$req->expires_in = 1000000;

        $user =(new UserModel())->otherlogin();
        return self::showResCode('登录成功',$user);
    }
  
}
