<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Db;
// 应用公共文件
use PHPMailer\PHPMailer\PHPMailer;
use app\lib\exception\BaseException;


// 异常类输出函数
function TApiException($msg = '异常', $errorCode = 999, $code = 400){
    throw new \app\lib\exception\BaseException(['code'=>$code,'msg'=>$msg,'errorCode'=>$errorCode]);
}

// 获取文件完整url
function getFileUrl($url='')
{
    if (!$url) return;
    return url($url,'',false,true);
}

// 检查链接是否404
function chkurl($url){
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);//设置超时时间
    curl_exec($handle);
    //检查是否404（网页找不到）
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if($httpCode == 404) {
      return false;
    }else{
        return true;
    }
 }

// 获取网页重定向后的链接
function get_redirect_url($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.119 Safari/537.36');
    $content = curl_exec($ch);
    $redirect_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $redirect_url = ($redirect_url == $url) ? $url : $redirect_url;
    curl_close($ch);
    return $redirect_url;
}

// 通用请求
function httpWurl($url, $params, $method = 'GET', $header = array(), $referer = '', $multi = false){
    date_default_timezone_set('PRC');
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => $header,
        CURLOPT_REFERER        => $referer,
        CURLOPT_COOKIESESSION  => true,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIE         => session_name().'='.session_id()
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            // $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            // 链接后拼接参数  &  非？
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
        TApiException('不支持的请求方式！');
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error) TApiException('请求发生错误：' . $error);
    return  $data;
}

function gen_params($params=array()){
    $arr = $params;
    $res = '?';
    foreach($arr as $key => $value){
        $res = $res.$key.'='.$value.'&';
    }
    return $res;
}

// 请求python api
function hshaApi($url='', $params=array(), $ahe=true, $method='POST', $baseUrl=''){
    // print_r(request()->param()['user_id']);
    // halt(request()->userId);
    // TApiException('维护中，暂停使用');
    $api_prefix = $baseUrl;
    if (!$api_prefix) {
        // 从数据库获取api基址
        // $eamsInfo = Db::table('userinfo')->where('user_id', request()->param()['user_id'])->find();
        // $api_prefix = Db::connect(config('sysconfig.sys_database'))->table('school_api')->where('school_id', $eamsInfo['user_school_id'])->find()['api_base_url'];
        // hpu api基址
        $api_prefix = "http://localhost:5022";
    } 
    // print_r($api_prefix);
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER     => array('Expect:')
    );
    
    // 链接后拼接参数  &  非？
    $opts[CURLOPT_URL] = $api_prefix . $url . gen_params($params);

    // print_r($opts[CURLOPT_URL]);

    $method='POST'?($opts[CURLOPT_POST] = 1):[];
    // $opts[CURLOPT_POST] = 1;

    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if($error) TApiException('请求发生错误：' . $error);

    // halt($ch);

    $res = json_decode($data, true);

    // halt($res);

    if($ahe&&array_key_exists('error_code', $res)) TApiException($res['msg'],30008,200);

    return $res;
}

// 执行js
function execJS($files,$params){
    $JS = new COM("MSScriptControl.ScriptControl");
    $JS->Language = "JavaScript";
    $JS->AllowUI = false;
    for ($i=0; $i < count($files); $i++) { 
        $jsData = file_get_contents($files[i]); //同级目录下的JS
        $JS->AddCode("$jsData");
    }
    $JS->run();
    return $JS;
}

function sendEmail($email='', $title='', $from_name='', $content='', $attachmentFile=''){
    $data['mail_from'] = "hsh_support@linkus.ren";
    $data['password'] = "hshSupport456";
    $data['subtype'] = "html";
    $data['mail_to'] = $email;
    $data['subject'] = $title;
    $data['content'] = $content;
    $re = httpWurl("http://45.137.154.217:8766/mail_sys/send_mail_http.json", $data, 'POST');
    $res = json_decode($re, true);
    if (!$res['status']) {
        throw new BaseException(['code'=>200,'msg'=>$res['msg'],'errorCode'=>30014]);
    } else {
        $status = 1;
        $data = "邮件发送成功";   
    } 
    return ['status'=>$status,'data'=>$data];//返回值（可选）
}

/**
 * @function    sendEmail
 * @intro        发送邮件（带附件）
 * @param $email     接收邮箱
 * @param $title     邮件标题
 * @param $from_name     发件人
 * @param $content     邮件内容
 * @param $attachmentFile     附件 （string | array）
 * @return  array
 */
function sendEmail163($email='', $title='', $from_name='', $content='', $attachmentFile=''){
    date_default_timezone_set('PRC');       
    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    //Tell PHPMailer to use SMTP
    $mail->isSMTP();
    $mail->SMTPSecure = 'ssl';  //这步是关键
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';
    //charset
    $mail->CharSet = 'UTF-8';
    //Set the hostname of the mail server
    $mail->Host = "smtp.163.com";//请填写你的邮箱服务器
    //Set the SMTP port number - likely to be 25, 465 or 587
    $mail->Port = 465;//端口号
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication
    $mail->Username = "hpubox@163.com";//发件邮箱用户名
    //Password to use for SMTP authentication
    $mail->Password = "HpuBox456api";//发件邮箱密码
    //Set who the message is to be sent from
    $mail->setFrom('hpubox@163.com', $from_name);
    //Set an alternative reply-to address(用户直接回复邮件的地址)
    $mail->addReplyTo('hpubox@163.com', $from_name);
    //Set who the message is to be sent to
    $mail->addAddress($email);
    //Set the subject line
    $mail->Subject = $title;
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->msgHTML($content);
    //Replace the plain text body with one created manually
    $mail->AltBody = '';
    if(is_array($attachmentFile)){
        for ($i=0; $i < count($attachmentFile); $i++) { 
            $mail->addAttachment($attachmentFile[$i],'Filename'.$i);//这里可以是多维数组，然后循环附件的文件和名称
        }
    }else{
        if($attachmentFile !=''){
            //Attach an image file
            $mail->addAttachment($attachmentFile, 'Filename');
        }
    }
    //send the message, check for errors
    if (!$mail->send()) {
        $status = 0;
        $data = "邮件发送失败" . $mail->ErrorInfo;;
    } else {
        $status = 1;
        $data = "邮件发送成功";   
    } 
    return ['status'=>$status,'data'=>$data];//返回值（可选）
}
