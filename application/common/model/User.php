<?php

namespace app\common\model;

use think\Model;
use think\Db;
use think\facade\Cache;
use app\lib\exception\BaseException;
use app\common\controller\AliSMSController;
use PHPMailer\PHPMailer\PHPMailer;

use app\common\model\App as AppModel;

class User extends Model
{
    // 自动写入时间
    protected $autoWriteTimestamp = true;

    // 关联文章
    public function post(){
        return $this->hasMany('Post');
    }

    // 关联课程表表
    public function usertimetale(){
        return $this->hasMany('UserTimetable');
    }

    // 同步微信资料 
    public function syncWx(){
        // 获取所有参数
        $params = request()->param();
        // print_r($params);
        return $params;
    }

    // 用户修改院校
    public function schoolSet(){
        
        // 获取所有参数
        $params = request()->param();
        if(!array_key_exists("school_id", $params)) TApiException('请选择院校',30031,200);
        // 获取用户id
        $userid=request()->userId;
        // 修改昵称
        $user = $this->get($userid);
        // 修改用户信息表
        $userinfo = $user->userinfo()->find();
        $userinfo['user_school_id'] = $params['school_id'];
        $userinfo->save();
        return true;
    }

    // 修改课程表
    public function editTimeTable(){
        // 获取所有参数
        $params = request()->param();
        $type = $params['type'];
        unset($params['type']);
        unset($params['version']);
        unset($params['userToken']);
        $params['user_id'] = $params['userId'];
        unset($params['userId']);
        unset($params['userTokenUserInfo']);
        // 判断权限
        if($type != 'add'){
            $target = Db::table('user_timetable')->where('id',$params['id'])->find();
            if(!$target && $type == 'delete'){
                $data['id'] = $params['id'];
                return $data;
            }
            if(!$target) {
                $type = 'add';
                unset($params['id']);
            }
            if($target && $params['user_id']!=$target['user_id']) return TApiException('操作失败',30019,200);
        }
        // return $params;
        // print_r((int)$params['id']);
        if($type == 'add'){
            // halt($params);
            unset($params['id']);
            $res = Db::name('user_timetable')->insertGetId($params);
            $data['id'] = $res;
            return $data;
        } 
        else if($type == 'edit'){
            $res = $this -> usertimetale()
            ->update($params);
            $data['id'] = $res['id'];
            return $data;
        }
        else if($type == 'delete'){
            Db::table('user_timetable')
            ->where('id', $params['id'])
            ->delete();
            $data['id'] = $params['id'];
            return $data;
        }
    }

    // 查空教室
    public function getEmptyClassroom(){
        // 获取所有参数
        $params = request()->param();
        // print_r($params);
        $res = Db::table('empty_classroom')
        ->where('campus',$params['campus'])
        ->where('building',$params['building'])
        ->where('schoolww',$params['schoolww'])
        ->where('week',$params['week'])
        ->field('section')
        ->field('classroom')
        ->select();
        return $res;
    }

    // 获取科目排名
    public function getCourseRanking(){

        // 功能维护
        // /*
        return [
            "major_rank" => "功能维护中",
            "academy_rank" => "功能维护中",
            "school_rank" => "功能维护中"
        ];
        // */

        $course = request()->param('coursename');
        $semester = request()->param('semester');
        $currentUserId = request()->userId;
        $currentUserSchoolBind = Db::table('user_eams_bind')->where('user_id',$currentUserId)->find();
        $currentUserMajor = $currentUserSchoolBind['major'];
        // print_r($currentUserMajor);
        $currentUserAcademy = $currentUserSchoolBind['academy'];
        $currentUserGrade = $currentUserSchoolBind['grade'];
        $currentUserNum = $currentUserSchoolBind['jwzh'];
        $data = [];
        $result = Db::table('user_score')
            ->where('type','1')
            ->where('grade',$currentUserGrade)
            ->where('major',$currentUserMajor)
            ->where('semester',$semester)
            ->where('course',$course)
            ->order('grade_point desc')
            ->select();
        $rank = 0;
        if(count($result)==0){$data['major_rank'] = null;} 
        foreach ($result as $item) {
            $rank = $rank + 1;
            if($item['stu_num'] == $currentUserNum){
                $data['major_rank'] = $rank;
            }
        }
        $result = Db::table('user_score')
            ->where('type','1')
            ->where('grade',$currentUserGrade)
            ->where('semester',$semester)
            ->where('academy',$currentUserAcademy)
            ->where('course',$course)
            ->order('grade_point desc')
            ->select();
        $rank = 0;
        if(count($result)==0){$data['academy_rank'] = null;} 
        foreach ($result as $item) {
            $rank = $rank + 1;
            if($item['stu_num'] == $currentUserNum){
                $data['academy_rank'] = $rank;
            }
        }
        $result = Db::table('user_score')
            ->where('type','1')
            ->where('grade',$currentUserGrade)
            ->where('semester',$semester)
            ->where('course',$course)
            ->order('grade_point desc')
            ->select();
        $rank = 0;
        if(count($result)==0){$data['school_rank'] = null;} 
        foreach ($result as $item) {
            $rank = $rank + 1;
            if($item['stu_num'] == $currentUserNum){
                $data['school_rank'] = $rank;
            }
        }
        return $data;
    }

    // 取学期GPA排名前100名 传入type 获取不同类型 0：专业，1：学院，2：学校
    public function getTopRanking(){
        $params = request()->param();
        if(!array_key_exists("test", $params)) TApiException('功能维护中',30008,200);
        $type = $params["type"];
        $currentUserId = request()->userId;
        $semest = $params["semster"];
        $mode = array_key_exists("mode", $params)?$params["mode"]:0;
        $limit = array_key_exists("limit", $params)?$params["limit"]:0;
        // print_r($currentUserId);
        $currentUserSchoolBind = Db::table('user_eams_bind')->where('user_id',$currentUserId)->find();
        $currentUserMajor = $currentUserSchoolBind['major'];
        $currentUserAcademy = $currentUserSchoolBind['academy'];
        $currentUserGrade = $currentUserSchoolBind['grade'];
        $currentUserNum = $currentUserSchoolBind['jwzh'];

        $maxQueryNum = 100;
        // print_r($currentUserMajor);
        if($type == 2){
            // 前100名
            $result = Db::table('user_score')
            ->where('type','2')
            ->where('grade',$currentUserGrade)
            ->where('semester', $semest)
            ->order('gpa desc')
            ->field('stu_num,gpa')
            ->limit($limit==0?0:$maxQueryNum)
            ->select();
            $data = [];
            $rank = 0;
            //我的gpa
            $my_rank = Db::table('user_score')
            ->where('type','2')
            ->where('stu_num',$currentUserNum)
            ->where('semester', $semest)
            ->field('stu_num,gpa')
            ->find();
            $my_rank['rank'] = $mode==0?">".$maxQueryNum:$maxQueryNum."名外";
            //遍历数据
            foreach ($result as $item) {
                $rank = $rank + 1;
                if($item['stu_num'] == $currentUserNum){
                    $item['rank'] = $mode==0?$rank:"第".$rank."名";
                    $my_rank=$item;
                }
                if($rank < 101){
                    $get_info = Db::table('user_eams_bind')->where('jwzh',$item['stu_num'])->find();
                    array_push($item,$get_info);
                    $item['rank'] = $mode==0?$rank:"第".$rank."名";
                    $item['stu_info'] = $item['0'];
                    $item['stu_info']['name'] = '某同学';
                    unset($item['0']);
                    array_push($data,$item);
                }
            }
            $f_data = [];
            $data = array_slice($data,0,100);
            array_push($f_data,[$my_rank]);
            array_push($f_data,$data);
            return $f_data;
        }
        if($type == 1){
            $result = Db::table('user_score')
            ->where('type','2')
            ->where('grade',$currentUserGrade)
            ->where('academy',$currentUserAcademy)
            ->where('semester', $semest)
            ->order('gpa desc')
            ->field('stu_num,gpa')
            ->select();
            $data = [];
            $rank = 0;
            $my_rank = [];
            foreach ($result as $item) {
                $rank = $rank + 1;
                if($item['stu_num'] == $currentUserNum){
                    $item['rank'] = $rank;
                    array_push($my_rank,$item);
                }
                if($rank < 101){
                    $get_info = Db::table('user_eams_bind')->where('jwzh',$item['stu_num'])->find();
                    array_push($item,$get_info);
                    $item['rank'] = $rank;
                    $item['stu_info'] = $item['0'];
                    $item['stu_info']['name'] = '某同学';
                    unset($item['0']);
                    array_push($data,$item);
                }
            }
            $f_data = [];
            $data = array_slice($data,0,100);
            array_push($f_data,$my_rank);
            array_push($f_data,$data);
            return $f_data;
        }
        if($type == 0){
            $result = Db::table('user_score')
            ->where('type','2')
            ->where('grade',$currentUserGrade)
            ->where('major',$currentUserMajor)
            ->where('semester', $semest)
            ->order('gpa desc')
            ->field('stu_num,gpa')
            ->select();
            $data = [];
            $rank = 0;
            //我的gpa
            $my_rank = Db::table('user_score')
            ->where('type','2')
            ->where('stu_num',$currentUserNum)
            ->where('semester', $semest)
            ->field('stu_num,gpa')
            ->find();
            $my_rank['rank'] = $mode==0?">".$maxQueryNum:$maxQueryNum."名外";
            foreach ($result as $item) {
                $rank = $rank + 1;
                if($item['stu_num'] == $currentUserNum){
                    $item['rank'] = $rank;
                    $my_rank=$item;
                }
                if($rank < 101){
                    $get_info = Db::table('user_eams_bind')->where('jwzh',$item['stu_num'])->find();
                    array_push($item,$get_info);
                    $item['rank'] = $rank;
                    $item['stu_info'] = $item['0'];
                    $item['stu_info']['name'] = '某同学';
                    unset($item['0']);
                    array_push($data,$item);
                }
            }
            $f_data = [];
            $data = array_slice($data,0,100);
            array_push($f_data,$my_rank);
            array_push($f_data,$data);
            return $f_data;
        }
    }

     // 解析图书馆图书页面
    public function ParsePage(){
        // 获取所有参数
        $url = request()->param('url');
        if(!strpos($url,'BookDetail')) return TApiException('页面错误',30016,200);
        $result = Db::table('prase_book_url')->where('m_url',$url)->where('status','1')->find();
        if($result['isbn'] == '-1') return TApiException('没有ISBN',30016,200);
        if($result) return $result['douban_url'];
        exec("python3 ../python/parse_book_url_server.py {$url}",$out,$res);
        if(!$out) return TApiException('获取豆瓣图书信息失败',30008,200);
        if($out[0] == 'url error') return TApiException('页面错误',30016,200);
        if($out[0] == 'no isbn found') return TApiException('没有ISBN',30016,200);
        if($out[0] == 'no book info') return TApiException('没有豆瓣图书信息',30016,200);
        if($out[0] == 'isbn error') return TApiException('ISBN错误',30016,200);
        return $out[0];
    }

    // 发布校园卡寻找失主
    public function findCard(){
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        $cardnum = request()->param('cardnum');
        $contactway = request()->param('contactway');
        $selfpick = request()->param('selfpick');
        if(mb_strlen($contactway)==0 && mb_strlen($selfpick)==0) 
        throw new BaseException(['code'=>200,'msg'=>'联系方式和自取地点至少填写一项','errorCode'=>30014]);
        $describe = request()->param('describe');
        $content = '<p>卡号：'.$cardnum.'（请确认卡号）</p><p>拾取人联系方式：'.($contactway?$contactway:'无').'</p><p>自取地点：'.($selfpick?$selfpick:'无').'</p><p>描述：'.($describe?$describe:'无').'</p>';
        $getinfo = Db::table('user_eams_bind')->where('jwzh', $cardnum)->select();
        // print_r($getinfo);
        $send = 0;
        foreach ($getinfo as $item) {
            $data = Db::table('user')->where('id',$item['user_id'])->find();
            if(!$data['email']) continue;
            if($data['email']){
                $res = sendEmail($data['email'], 'HPU校园助手 | 有人捡到了你的校园卡，点击查看详情', 'hsh_support' , $content);
                if($res['status'] == 0) throw new BaseException(['code'=>200,'msg'=>'发布失败，请稍后再试','errorCode'=>30014]);
                Db::table('findcard_record')->insert(['pusher_id'=>$currentUserId,'target_id'=>$item['user_id'],'email'=>$data['email'],'cardnum'=>$cardnum,'pusher_contact'=>$contactway,'self_pick'=>$selfpick,'describe'=>$describe]);
                $send = $send + 1;
            }
        }
        if($send == 0) throw new BaseException(['code'=>200,'msg'=>'该校园卡暂时未被绑定，发布失败','errorCode'=>30021]);
        return $send;
    }

    // 发送邮箱验证码
    public function sendEmailCode(){
        // print_r('here');
        $email = request()->param('email');
        // 判断是否已经发送过
        if(Cache::get($email.'flag')) throw new BaseException(['code'=>200,'msg'=>'你操作得太快了','errorCode'=>30001]);
        // 生成4位验证码
        $code = random_int(1000,9999);
        //sendEmail($email='', $title='', $from_name='', $content='11', $attachmentFile='')
        $res = sendEmail($email, 'HPU校园助手 | 查收您的验证码', 'HPU校园助手 | 验证码：'.$code , '您本次操作的验证码为：'.$code.'，验证码30分钟内有效，如非本人操作，请忽略本条信息');  
        if($res['status'] == 1){
            Cache::set($email.'flag',1,60); // return Cache::set($email,$code,30);
            Cache::set($email,$code,1800);
        } 
        if($res['status'] == 0) throw new BaseException(['code'=>200,'msg'=>'发送失败，请检查邮箱或稍后再试','errorCode'=>30014]);
    }

    //发送验证码
    public function sendCode(){
        // 获取用户提交手机号码
        $phone = request()->param('phone');
        // 判断是否已经发送过
        if(Cache::get($phone)) throw new BaseException(['code'=>200,'msg'=>'你操作得太快了','errorCode'=>30001]);
        // 生成4位验证码
        $code = random_int(1000,9999);
        // 判断是否开启验证码功能
        if(!config('api.aliSMS.isopen')){
            // Cache::set($phone,$code,config('api.aliSMS.expire'));
            throw new BaseException(['code'=>200,'msg'=>'服务未开启','errorCode'=>30005]);
        }
        // 发送验证码
        $res = AliSMSController::SendSMS($phone,$code);
        //发送成功 写入缓存
        if($res['Code']=='OK') return Cache::set($phone,$code,config('api.aliSMS.expire'));
        // 无效号码
        if($res['Code']=='isv.MOBILE_NUMBER_ILLEGAL') throw new BaseException(['code'=>200,'msg'=>'无效号码','errorCode'=>30002]);
        // 触发日限制
        if($res['Code']=='isv.DAY_LIMIT_CONTROL') throw new BaseException(['code'=>200,'msg'=>'今日你已经发送超过限制','errorCode'=>30003]);
        // 发送失败
        throw new BaseException(['code'=>200,'msg'=>'发送失败','errorCode'=>30004]);
    }

    // 绑定用户信息表
    public function userinfo(){
        return $this->hasOne('Userinfo');
    }

    // 绑定第三方登录
    public function userbind(){
        return $this->hasMany('UserBind');
    }

    // 获取成绩
    public function getGrade(){
        // 获取所有参数
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // $user = Db::table('user_eams_bind')->where('user_id',$currentUserId)->find();
        //print_r($user['jwzh']);
        $result = Db::table('user_score')->where('user_id',$currentUserId)->where('type','1')->select();
        $gpa = Db::table('user_score')->where('user_id',$currentUserId)->where('type','2')->select();
        $data = [];
        array_push($data,$gpa);
        array_push($data,$result);
        return $data;
    }

    // 获取单日课程表
    public function getDayTimeTable(){
        // 获取所有参数
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        $arr = [];
        for ($i=1; $i < 8; $i++) { 
            $result = Db::table('user_timetable')->where('user_id',$currentUserId)->where('week',$i)->select();
            array_push($arr,$result);
        }
        return $arr;
    }

    // 获取课程表
    public function getTimeTable(){
        // 获取所有参数
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        $result = Db::table('user_timetable')->where('user_id',$currentUserId)->select();
        return $result; 
    }


    // 获取借阅图书
    public function getBorrowBook(){
        // 获取所有参数
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        $result = Db::table('user_borrow_book')->where('user_id',$currentUserId)->select();
        $data = [];
        foreach ($result as $item) {
            $get_info = Db::table('book_info')->where('isbn',$item['isbn'])->find();
            array_push($item,$get_info);
            $item['bookinfo'] = $item['0'];
            unset($item['0']);
            array_push($data,$item);
        };
        return $data;
    }
    
    // 绑定图书馆
    public function loanList(){
        // 获取所有参数
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        $clientip = request()->ip();

        $db = Db::connect(config('sysconfig.sys_database'));     
        $db_res = $db->table('library_session')->where('user_id', $currentUserId)->find();

        // return $db_res;

        $res = hshaApi(
            '/lib/login',
            [ 
                'session' => $db_res['content'],
                'username' => $params['usernum'],
                'password' => $params['password'],
                'captcha' => $params['captcha'],
                'codeKey' => $db_res['code_key'],
                'ip' => $clientip
            ]
        );

        // return $res['data'];
        Db::table('user_library_bind')->where('user_id',$currentUserId)->delete();
        Db::table('user_library_bind')->insert([
            'user_id' => $currentUserId,
            'usernum' => $params['usernum'],
            'status' => 1
        ]);

        $res2 = hshaApi(
            '/lib/loanList',
            [ 
                'session' => $res['data'],
                'ip' => $clientip
            ]
        );

        $result = $res2['data']['searchResult'];
        $numFound = $res2['data']['numFound'];

        for ($i=0; $i < $numFound; $i++) { 
            $item = $result[$i];
            // halt($item);
            $cover = hshaApi(
                '/lib/book/cover/normal',
                [ 
                    'title' => urlencode($item['title']),
                    'isbn' => urlencode($item['isbn'])
                ]
            );
            $result[$i]['bookCover'] = $cover['data'];
        }

        return $result;

    }

    // 绑定学号、教务处&认证
    public function eamsBind(){
        // 获取所有参数
        $params = request()->param();
        $currentUserId = request()->userId;
        $clientip = request()->ip();
        
        if(!array_key_exists("v2", $params)) TApiException('请升级版本',30008,200);
        // TApiException('功能维护中',30008,200);
        if(!array_key_exists("type", $params)) return TApiException('缺少参数: type',30009,200);
        $semesterId = array_key_exists("semester_id", $params)&&$params['semester_id']?$params['semester_id']:62;

        $session = (new AppModel())->eamsLogin();

        $res = hshaApi(
            '/eams/bind',
            [ 
                'session' => $session,
                'semesterId' => $semesterId,
                'type' => $params['type'],
                'ip' => $clientip
            ]
        );

        // return $res;

        $stu_score = $res['stu_score'];
        $stu_info = $res['stu_info'];
        $stu_timetable = $res['stu_timetable'];

        if ($stu_score) {
            // print_r('成绩有数据');
            Db::table('user_score')->where('user_id', $currentUserId)->delete();
            for ($ss_i=0; $ss_i < count($stu_score); $ss_i++) { 
                $stu_score[$ss_i]['user_id'] = $currentUserId;
                Db::table('user_score')->insert($stu_score[$ss_i]);
            }
        }
        if ($stu_info) {
            // print_r('信息有数据');
            Db::table('user_eams_bind')->where('user_id', $currentUserId)->delete();
            $ins['user_id'] = $currentUserId;
            $ins['name'] = $stu_info['姓名：'];
            $ins['sex'] = $stu_info['性别：'];
            $ins['grade'] = $stu_info['所在年级：'];
            $ins['academy'] = $stu_info['行政管理院系：'];
            $ins['major'] = $stu_info['专业：'];
            $ins['class'] = $stu_info['行政班级：'];
            $ins['jwzh'] = $stu_info['学号：'];
            $ins['status'] = 1;
            Db::table('user_eams_bind')->insert($ins);
        }
        if ($stu_timetable) {
            // print_r('课程表有数据');
            Db::table('user_timetable')->where('user_id', $currentUserId)->delete();
            if($stu_timetable != 'empty'){
                for ($st_i=0; $st_i < count($stu_timetable); $st_i++) { 
                    $stu_timetable[$st_i]['user_id'] = $currentUserId;
                    Db::table('user_timetable')->insert($stu_timetable[$st_i]);
                }
            }
        }

        $user = Db::table('user_eams_bind')->where('user_id',$currentUserId)->find();
        return $user;
    }

    // 判断用户是否存在
    public function isExist($arr=[]){
        if(!is_array($arr)) return false;
        if (array_key_exists('phone',$arr)) { // 手机号码
            $user = $this->where('phone',$arr['phone'])->find();
            if ($user) $user->logintype = 'phone';
            return $user;
        }
        // 用户id
        if (array_key_exists('id',$arr)) { // 用户名
            return $this->where('id',$arr['id'])->find();
        }
        if (array_key_exists('email',$arr)) { // 邮箱
            $user = $this->where('email',$arr['email'])->find();
            if ($user) $user->logintype = 'email';
            return $user;
        }
        if (array_key_exists('username',$arr)) { // 用户名
            $user = $this->where('username',$arr['username'])->find();
            if ($user) $user->logintype = 'username';
            return $user;
        }
        // 第三方参数
        if (array_key_exists('provider',$arr)) {
            $where = [
                'type'=>$arr['provider'],
                'openid'=>$arr['openid']
            ];
            $user = $this->userbind()->where($where)->find();
            if ($user) $user->logintype = $arr['provider'];
            return $user;
        }
        return false;
    }

    // 邮箱验证码登录
    public function emailLogin($isWxmp = false){
        // 获取所有参数
        $param = request()->param();
        // 验证用户是否存在
        $user = $this->isExist(['email'=>$param['email']]);
        if($isWxmp && !$user) throw new BaseException(['code'=>200,'msg'=>'邮箱未被绑定，请使用微信登录注册','errorCode'=>20022]);
        // 用户不存在，直接注册
        if(!$user){
            // 用户主表
            $user = self::create([
                'username'=>mb_substr($param['email'],0,7),
                'userpic' => 'https://hpubox.oss-cn-shanghai.aliyuncs.com/appsource/Static/fixed/default-upic.png',
                'userbgpic' => 'https://hpubox.oss-cn-shanghai.aliyuncs.com/appsource/Static/fixed/default-ubgpic.jpg',
                'email'=>$param['email'],
                // 'password'=>password_hash($param['phone'],PASSWORD_DEFAULT)
            ]);
            // 在用户信息表创建对应的记录（用户存放用户其他信息）
            $user->userinfo()->create([ 
                'user_id'=>$user->id,
                'birthday' => date("Y-m-d")
            ]);
            $user->logintype = 'email';
            $user['username'] = mb_substr($user['username'],0,7);
            $userarr = $user->toArray();
            $userarr['token'] = $this->CreateSaveToken($userarr);
            $userarr['userinfo'] = $user->userinfo->toArray();
            $userarr['schoolbind'] = false;
            $userarr['libbind'] = false;
            $userarr['password'] = false;
         	return $userarr;
        }
        // 用户是否被禁用
        $this->checkStatus($user->toArray());
        // 登录成功，返回token和用户信息
        $schoolbind = Db::table('user_eams_bind')->where('user_id',$user->id)->find();
        $libbind = Db::table('user_library_bind')->where('user_id',$user->id)->find();
        $userarr = $user->toArray();
        $userarr['token'] = $this->CreateSaveToken($userarr);
        $userarr['userinfo'] = $user->userinfo->toArray();
        $userarr['schoolbind'] = $schoolbind ? $schoolbind : false;
        $userarr['libbind'] = $libbind ? $libbind : false;
        $userarr['password'] = $userarr['password'] ? true : false;
        return $userarr;
    }


    // 手机验证码登录
    public function phoneLogin(){
        // 获取所有参数
        $param = request()->param();
        // 验证用户是否存在
        $user = $this->isExist(['phone'=>$param['phone']]);
        // 用户不存在，直接注册
        if(!$user){
            // 用户主表
            $user = self::create([
                'username'=>$param['phone'],
                'userpic' => 'https://hpubox.oss-cn-shanghai.aliyuncs.com/appsource/Static/fixed/default-upic.png',
                'userbgpic' => 'https://hpubox.oss-cn-shanghai.aliyuncs.com/appsource/Static/fixed/default-ubgpic.jpg',
                'phone'=>$param['phone'],
                // 'password'=>password_hash($param['phone'],PASSWORD_DEFAULT)
            ]);
            // 在用户信息表创建对应的记录（用户存放用户其他信息）
            $user->userinfo()->create([ 
                'user_id'=>$user->id,
                'birthday' => date("Y-m-d")
            ]);
            $user->logintype = 'phone';
            $user['username'] = mb_substr($user['username'],0,7);
            $userarr = $user->toArray();
            $userarr['token'] = $this->CreateSaveToken($userarr);
            $userarr['userinfo'] = $user->userinfo->toArray();
            $userarr['schoolbind'] = false;
            $userarr['libbind'] = false;
            $userarr['password'] = false;
         	return $userarr;
        }
        // 用户是否被禁用
        $this->checkStatus($user->toArray());
        // 登录成功，返回token和用户信息
        $schoolbind = Db::table('user_eams_bind')->where('user_id',$user->id)->find();
        $libbind = Db::table('user_library_bind')->where('user_id',$user->id)->find();
        $userarr = $user->toArray();
        $userarr['token'] = $this->CreateSaveToken($userarr);
        $userarr['userinfo'] = $user->userinfo->toArray();
        $userarr['schoolbind'] = $schoolbind ? $schoolbind : false;
        $userarr['libbind'] = $libbind ? $libbind : false;
        $userarr['password'] = $userarr['password'] ? true : false;
        return $userarr;
    }

    // 生成并保存token
    public function CreateSaveToken($arr=[]){
        // 生成token
        $token = sha1(md5(uniqid(md5(microtime(true)),true)));
        $arr['token'] = $token;
        // 登录过期时间
        $expire =array_key_exists('expires_in',$arr) ? $arr['expires_in'] : config('api.token_expire');
        // 保存到缓存中
        if (!Cache::set($token,$arr,$expire)) throw new BaseException();
        // 返回token
        return $token;
    }

    // 用户是否被禁用
    public function checkStatus($arr,$isReget = false){
        $status = 1;
        if ($isReget) {
            // 账号密码登录 和 第三方登录
            $userid = array_key_exists('user_id',$arr)?$arr['user_id']:$arr['id'];
            // 判断第三方登录是否绑定了手机号码
            if ($userid < 1) return $arr;
            // 查询user表
            $user = $this->find($userid);
            if(!$user){throw new BaseException(['code'=>200,'msg'=>'用户不存在','errorCode'=>20001]);}
            $user = $this->find($userid)->toArray();
            // 拿到status
            $status = $user['status'];
        }else{
            $status = $arr['status'];
        }
        if($status==0) throw new BaseException(['code'=>200,'msg'=>'该用户已被禁用','errorCode'=>20001]);
        return $arr;
    }


    // 账号登录
    public function login(){
        // 获取所有参数
        $param = request()->param();
        // 验证用户是否存在
        // $user = $this->isExist($this->filterUserData($param['username']));
        $arr = [];
        $arr[$param['type']] = $param['username'];
        $user = $this->isExist($arr);
        $user->logintype = $param['type'];
        // 用户不存在
        if(!$user) throw new BaseException(['code'=>200,'msg'=>'账号错误','errorCode'=>20000]);
        // 用户是否被禁用
        $this->checkStatus($user->toArray());
        // 验证密码
        $this->checkPassword($param['password'],$user->password);
        // 登录成功 生成token，进行缓存，返回客户端
        $schoolbind = Db::table('user_eams_bind')->where('user_id',$user->id)->find();
        $libbind = Db::table('user_library_bind')->where('user_id',$user->id)->find();
        $userarr = $user->toArray();
        $userarr['token'] = $this->CreateSaveToken($userarr);
        $userarr['userinfo'] = $user->userinfo->toArray();
        $userarr['schoolbind'] = $schoolbind ? $schoolbind : false;
        $userarr['libbind'] = $libbind ? $libbind : false;
        $userarr['password'] = $userarr['password'] ? true : false;
        return $userarr;
    }

    // 验证用户名是什么格式，昵称/邮箱/手机号
    public function filterUserData($data){
        $arr=[];
        // 验证是否是手机号码
        if(preg_match('^1(3|4|5|7|8)[0-9]\d{8}$^', $data)){
            $arr['phone']=$data; 
            return $arr;
        }
        // 验证是否是邮箱
        if(preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', $data)){
            $arr['email']=$data; 
            return $arr;
        }
        $arr['username']=$data; 
        return $arr;
    }

    // 验证密码
    public function checkPassword($password,$hash){
        if (!$hash) throw new BaseException(['code'=>200,'msg'=>'密码错误','errorCode'=>20002]);
        // 密码错误
        if(!password_verify($password,$hash)) throw new BaseException(['code'=>200,'msg'=>'密码错误','errorCode'=>20002]);
        return true;
    }


    // 微信小程序登录
    public function wxmplogin(){
        // 获取所有参数
        $param = request()->param();
        if($param['provider']!='weixin') TApiException('异常请求',30020);
        // 解密过程（待添加）
        // 验证用户是否存在
        $userbind = $this->isExist(['provider'=>'weixin','openid'=>$param['openid']]); // userbind中搜寻
        // 用户不存在，创建用户
        $arr = [];
        if (!$userbind) {
            $user = self::create([
                'username'=>$param['nickName'],
                'userpic' => $param['avatarUrl'],
                'userbgpic' => 'https://hpubox.oss-cn-shanghai.aliyuncs.com/appsource/Static/fixed/default-ubgpic.jpg',
                // 'password'=>password_hash($param['phone'],PASSWORD_DEFAULT)
            ]);
            $user->userbind()->create([
                'type'=>$param['provider'],
                'openid'=>$param['openid'],
                'nickname'=>$param['nickName'],
                'avatarurl'=>$param['avatarUrl'],
                'user_id'=>$user['id']
            ]);
            // 在用户信息表创建对应的记录（用户存放用户其他信息）
            $user->userinfo()->create([ 
                'user_id'=>$user->id,
                'sex'=>$param['sex'],
                'path'=>$param['path'],
                'birthday' => date("Y-m-d")
            ]);
            $user->logintype = 'weixin-mp';
            $arr = $user->toArray();
            $arr['expires_in'] = $param['expires_in']; 
            $arr['token'] = $this->CreateSaveToken($arr);
            $arr['userinfo'] = $user->userinfo->toArray();
            $arr['schoolbind'] = false;
            $arr['libbind'] = false;
            return $arr;
        }
        // 用户是否被禁用
        $user = $this->isExist(['id'=>$userbind['user_id']]);
        // $user->username = $param['nickName'];
        $user->userpic = $param['avatarUrl'];
        // $user->username = $param['nickName'];
        $user->logintype = 'weixin-mp';
        $userarr = $this->checkStatus($user->toArray());
        $user = self::update([
            'id'=>$userbind['user_id'],
            // 'username'=>$param['nickName'],
            'userpic' => $param['avatarUrl'],
            // 'password'=>password_hash($param['phone'],PASSWORD_DEFAULT)
        ]);
        $user->userbind()->update([
            'id'=>$userbind['id'],
            'type'=>$param['provider'],
            'openid'=>$param['openid'],
            'nickname'=>$param['nickName'],
            'avatarurl'=>$param['avatarUrl'],
            'user_id'=>$user['id']
        ]);
        // $user->userinfo()->update([ 
        //     'id'=>$user->userinfo()->where('user_id',$userbind['user_id'])->find()['id'],
        //     'sex'=>$param['sex'],
        //     'path'=>$param['path']
        // ]);
        // return $user;
        // 登录成功，返回token和用户信息
        $schoolbind = Db::table('user_eams_bind')->where('user_id',$user->id)->find();
        $libbind = Db::table('user_library_bind')->where('user_id',$user->id)->find();
        $userarr['token'] = $this->CreateSaveToken($userarr);
        $userarr['userinfo'] = $user->userinfo->toArray();
        $userarr['schoolbind'] = $schoolbind ? $schoolbind : false;
        $userarr['libbind'] = $libbind ? $libbind : false;
        $userarr['password'] = $userarr['password'] ? true : false;
        return $userarr;
    }


    // 验证第三方登录是否绑定手机
    public function OtherLoginIsBindPhone($user){
        // 验证是否是第三方登录
        if(array_key_exists('type',$user)){
            if($user['user_id']<1){
                throw new BaseException(['code'=>200,'msg'=>'请先绑定手机！','errorCode'=>20008]);
            }
            return $user['user_id'];
        }
        // 账号密码登录
        return $user['id'];
    }


    // 退出登录
    public function logout(){
        // 获取并清除缓存
        if (!Cache::pull(request()->userToken)) TApiException('你已经退出了',30006); return true;
    }

    // 举报用户、文章、评论 % 0 1 2
    public function Report(){
        $params = request()->param();
        $currentUserId = request()->userId;
        // print_r($params);
        if($params['type'] == 0){
            Db::table('reported_user')->insert(['report_user_id'=>$currentUserId,'user_id'=>$params['id']]);
        }
        if($params['type'] == 1){
            Db::table('reported_post')->insert(['report_user_id'=>$currentUserId,'post_id'=>$params['id']]);
        }
        if($params['type'] == 2){
            Db::table('reported_comment')->insert(['report_user_id'=>$currentUserId,'comment_id'=>$params['id']]);
        }
    }

    // 获取指定用户下文章
    public function getPostList(){
        $params = request()->param();
        $user = $this->get($params['id']);
        // 当前用户id
        $userId = request()->userId ? request()->userId : 0;
        if (!$user) TApiException('该用户不存在',10000);
        return $user->post()->with([
            'user'=>function($query) use($userId){
                return $query->field('id,username,userpic')->with([
                    'fens'=>function($query) use($userId){
                        return $query->where('user_id',$userId)->hidden(['password']);
                    },'userinfo'
                ]);
            },'images'=>function($query){
                return $query->field('url');
            },'share'
            ,'support'=>function($query) use($userId){
                return $query->where('user_id',$userId);
            }])->withCount(['Ding','Cai','comment'])->where('isopen',1)->page($params['page'],10)->order('create_time','desc')->select();
    }

    // 删除我的评论
    public function delMyComment(){
        $params = request()->param();
        if($this->DeleteComment($params['commentid'])){ return true; }
        else TApiException('删除失败',300018);
    }

    // 删除评论
    public function DeleteComment($id){
        $target = Db::table('comment')->where('id',$id)->find();
        if($target['fnum']==0 && $target['fid']==0){
            return Db::table('comment')->where('id',$id)->delete();
        }
        if($target['fnum']!=0 && $target['fid']==0){
            return Db::table('comment')->where('id',$id)->update(['data'=>null]);
        }
        if($target['fnum']==0 && $target['fid']!=0){
            $target_fid = Db::table('comment')->where('id',$target['fid'])->find();
            $this->UpdateFnum($target['fid']);
            Db::table('comment')->where('id',$id)->delete();
            return $this->DeleteNullComment($target['fid']);
        }
        if($target['fnum']!=0 && $target['fid']!=0){
            $target_fid = Db::table('comment')->where('id',$target['fid'])->find();
            $this->UpdateFnum($target['fid']);
            Db::table('comment')->where('id',$id)->update(['data'=>null]);
            return $this->DeleteNullComment($target['fid']);
        }
        TApiException('删除失败',300018);
    }

    // 更新fnum （递减 1）
    public function UpdateFnum($id){
        $comment = Db::table('comment')->where('id',$id)->find();
        Db::table('comment')->where('id',$id)->update(['fnum'=>$comment['fnum']-1]);
        if($comment['fid']>0){
            return $this->UpdateFnum($comment['fid']);
        }
        return true;
    }

    // 删除空 (null) 评论
    public function DeleteNullComment($id){
        $target = Db::table('comment')->where('id',$id)->find();
        if($target['fnum']==0 && $target['data']==null){
            Db::table('comment')->where('id',$id)->delete();
            if($target['fid']>0){
                $target_fid = Db::table('comment')->where('id',$target['fid'])->find();
                if($target_fid['data']==null){
                    return $this->DeleteNullComment($target_fid['id']);
                }
            }
        }
        return true;
    }

    // 删除我的动态
    public function delMyPost(){
        $params = request()->param();
        if($this->DeletePost($params['postid'])){ return true; }
        else TApiException('删除失败',300018);
    }

    // 删除动态 
    public function DeletePost($id){
        Db::table('post')->where('id',$id)->delete();
        Db::table('comment')->where('post_id',$id)->delete();
        Db::table('support')->where('post_id',$id)->delete();
        $post_imgs = Db::table('post_image')->where('post_id',$id)->select();
        foreach ($post_imgs as $item) {
            Db::table('images')->where('id',$item['image_id'])->update(['status'=> 0 ]);
        }
        return true;
    }

    // 获取指定用户下所有文章
    public function getAllPostList(){
        $params = request()->param();
        // 获取用户id
        $user_id = request()->userId;
        $userId = request()->userId ? request()->userId : 0;
        return $this->get($user_id)->post()->with([
            'user'=>function($query) use($userId){
                return $query->field('id,username,userpic')->with([
                    'fens'=>function($query) use($userId){
                        return $query->where('user_id',$userId)->hidden(['password']);
                    },'userinfo'
                ]);
            },'images'=>function($query){
                return $query->field('url');
            },'share'
            ,'support'=>function($query) use($userId){
                return $query->where('user_id',$userId);
            }])->withCount(['Ding','Cai','comment'])->page($params['page'],10)->order('create_time','desc')->select();
    }

    //  // 关联文章
    //  public function post(){
    //     return $this->hasMany('Post');
    // }

     // 绑定用户信息表
     public function support(){
        return $this->hasMany('Support');
    }

    // 获取指定用户被评论过的文章（分页）
    public function getbeReplyedPost(){
        // 获取所有参数
        $params = request()->param();
        $userId = request()->userId;
        // print_r($param);
        $result = $this->get($userId)->comments()->where('type','0')->select();
        $res_arr = [];
        foreach ($result as $item) {
            array_push($res_arr,$item['post_id']);
        }
        // print_r($result);
        return $this->post()->where('id','in',$res_arr)->with([
            'user'=>function($query) use($userId){
                return $query->field('id,username,userpic')->with([
                    'fens'=>function($query) use($userId){
                        return $query->where('user_id',$userId)->hidden(['password']);
                    },'userinfo'
                ]);
            },'images'=>function($query){
                return $query->field('url');
            },'share'
            ,'support'=>function($query) use($userId){
                return $query->where('user_id',$userId);
            }])->withCount(['Ding','Cai','comment'])->page($params['page'],10)->order('create_time','desc')->select();
    }

    // 获取指定用户赞过的文章（分页）
    public function getSupportedPost(){
        // 获取所有参数
        $params = request()->param();
        $userId = request()->userId;
        // print_r($param);
        $result = $this->get($userId)->support()->where('type','0')->select();
        $res_arr = [];
        foreach ($result as $item) {
            array_push($res_arr,$item['post_id']);
        }
        // print_r($result);
        return $this->post()->where('id','in',$res_arr)->with([
            'user'=>function($query) use($userId){
                return $query->field('id,username,userpic')->with([
                    'fens'=>function($query) use($userId){
                        return $query->where('user_id',$userId)->hidden(['password']);
                    },'userinfo'
                ]);
            },'images'=>function($query){
                return $query->field('url');
            },'share'
            ,'support'=>function($query) use($userId){
                return $query->where('user_id',$userId);
            }])->withCount(['Ding','Cai','comment'])->page($params['page'],10)->order('create_time','desc')->select();
    }

    // 获取指定用户评论过的文章（分页）
    public function getCommentedPost(){
        // 获取所有参数
        $params = request()->param();
        $userId = request()->userId;
        // print_r($param);
        $result = Db::table('comment')->where('user_id',$userId)->select();
        $res_arr = [];
        foreach ($result as $item) {
            if(!in_array($item['post_id'],$res_arr)){
                array_push($res_arr,$item['post_id']);
            }
        }
        // print_r($res_arr);
        return $this->post()->where('id','in',$res_arr)->with([
            'user'=>function($query) use($userId){
                return $query->field('id,username,userpic')->with([
                    'fens'=>function($query) use($userId){
                        return $query->where('user_id',$userId)->hidden(['password']);
                    },'userinfo'
                ]);
            },'images'=>function($query){
                return $query->field('url');
            },'share'
            ,'support'=>function($query) use($userId){
                return $query->where('user_id',$userId);
            }])->withCount(['Ding','Cai','comment'])->page($params['page'],10)->order('create_time','desc')->select();
    }

    // 搜索用户
    public function Search(){
        // 获取所有参数
        $param = request()->param();
        return $this->where('username','like','%'.$param['keyword'].'%')->with(['userinfo'])->page($param['page'],10)->hidden(['password'])->select();
    }

    // 验证当前绑定类型是否冲突
    public function checkBindType($current,$bindtype){
        // 当前绑定类型
        if($bindtype == $current) TApiException('绑定类型冲突');
        return true;
    }

    // 绑定手机
    public function bindphone(){
        // 获取所有参数
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType,'phone');
        // 查询该手机是否绑定了其他用户
        $binduser = $this->isExist(['phone'=>$params['phone']]);
        // 存在
        if ($binduser) {
            // 账号邮箱登录
            if ($currentLoginType == 'username' || $currentLoginType == 'email') TApiException('该手机号已绑定到其他账号',20006,200);
            // 第三方登录
            if ($binduser->userbind()->where('type',$currentLoginType)->find()) TApiException('该手机号已绑定到其他账号',20006,200);
            // 直接修改
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $binduser->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $binduser->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);

                $currentUserInfo['user'] = $binduser->toArray();
                $currentUserInfo['user']['userinfo'] = $binduser->userinfo->toArray();
                $currentUserInfo['user']['password'] = $currentUserInfo['user']['password'] ? true : false;

                return $currentUserInfo;
            }
            TApiException();
        }
        // 不存在
        // 账号邮箱登录
        if ($currentLoginType == 'username' || $currentLoginType == 'email'){
            $user = $this->save([
                'phone'=>$params['phone']
            ],['id'=>$currentUserId]);
            // 更新缓存
            $currentUserInfo['phone'] = $params['phone'];
            Cache::set($currentUserInfo['token'],$currentUserInfo,config('api.token_expire'));
            return true;
        }
        // 第三方登录
        if (!$currentUserId) {
            // 在user表创建账号
            $user = $this->create([
                'username'=>$params['phone'],
                'phone'=>$params['phone'],
            ]);
            // 在userinfo表创建记录
            $user->userinfo()->create([ 'user_id'=>$user->id ]);
            // 绑定
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $user->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $user->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);

                $currentUserInfo['user'] = $user->toArray();
                $currentUserInfo['user']['userinfo'] = $user->userinfo->toArray();
              	$currentUserInfo['user']['password'] = (array_key_exists('password',$currentUserInfo['user']) && $currentUserInfo['user']['password']) ? true : false;

                return $currentUserInfo;
            }
            TApiException();
        }
        // 直接修改
        if($this->save([
            'phone'=>$params['phone']
        ],['id'=>$currentUserId])) return true;
        TApiException();
    }

    // 更换绑定手机 $$
    public function changebindphone(){
        // 获取所有参数
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        // $this->checkBindType($currentLoginType,'phone');
        // 查询该手机是否绑定了其他用户
        $binduser = $this->isExist(['phone'=>$params['phone']]);
        // 存在
        if ($binduser) {
            // 账号邮箱登录
            if ($currentLoginType == 'username' || $currentLoginType == 'email' || $currentLoginType == 'phone') TApiException('该手机号已绑定到其他账号',20006,200);
            // 第三方登录
            if ($binduser->userbind()->where('type',$currentLoginType)->find()) TApiException('该手机号已绑定到其他账号',20006,200);
            // 直接修改
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $binduser->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $binduser->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);

                $currentUserInfo['user'] = $binduser->toArray();
                $currentUserInfo['user']['userinfo'] = $binduser->userinfo->toArray();
                $currentUserInfo['user']['password'] = $currentUserInfo['user']['password'] ? true : false;

                return $currentUserInfo;
            }
            TApiException();
        }
        // 不存在
        // 账号邮箱登录
        if ($currentLoginType == 'username' || $currentLoginType == 'email'){
            $user = $this->save([
                'phone'=>$params['phone']
            ],['id'=>$currentUserId]);
            // 更新缓存
            $currentUserInfo['phone'] = $params['phone'];
            Cache::set($currentUserInfo['token'],$currentUserInfo,config('api.token_expire'));
            return true;
        }
        // 第三方登录
        if (!$currentUserId) {
            // 在user表创建账号
            $user = $this->create([
                'username'=>$params['phone'],
                'phone'=>$params['phone'],
            ]);
            // 在userinfo表创建记录
            $user->userinfo()->create([ 'user_id'=>$user->id ]);
            // 绑定
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $user->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $user->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);

                $currentUserInfo['user'] = $user->toArray();
                $currentUserInfo['user']['userinfo'] = $user->userinfo->toArray();
              	$currentUserInfo['user']['password'] = (array_key_exists('password',$currentUserInfo['user']) && $currentUserInfo['user']['password']) ? true : false;

                return $currentUserInfo;
            }
            TApiException();
        }
        // 直接修改
        if($this->save([
            'phone'=>$params['phone']
        ],['id'=>$currentUserId])) return true;
        TApiException();
    }

    // 绑定邮箱
    public function bindemail(){
        // 获取所有参数
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType,'email');
        // 查询该手机是否绑定了其他用户
        $binduser = $this->isExist(['email'=>$params['email']]);
        // 存在
        if ($binduser) {
            // 账号手机邮箱小程序登录（高权）
            if ($currentLoginType == 'username' || $currentLoginType == 'phone' || $currentLoginType == 'weixin-mp' ) TApiException('该邮箱已被绑定到其他账号',20006,200);
            // 第三方登录
            if ($binduser->userbind()->where('type',$currentLoginType)->find()) TApiException('该邮箱已被绑定到其他账号',20006,200);
            // 直接修改（关联两个账号）
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $binduser->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $binduser->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        // 不存在
        // 第三方登录
        if (!$currentUserId) {
            // 在user表创建账号
            $user = $this->create([
                'username'=>mb_substr($params['email'],0,7),
                'email'=>$params['email'],
            ]);
            // 绑定
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $user->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $user->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        // 直接修改
        if($this->save([
            'email'=>$params['email']
        ],['id'=>$currentUserId])) return true;
        TApiException();
    }


    // 换绑邮箱
    public function changebindemail(){
        // 获取所有参数
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        $oldEmail = $params['oldemail'];
        if(!cache($oldEmail)) TApiException('请重新验证',20006,200);
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // // 验证绑定类型是否冲突
        // $this->checkBindType($currentLoginType,'email');
        // 查询该手机是否绑定了其他用户
        $binduser = $this->isExist(['email'=>$params['email']]);
        // 存在
        if ($binduser['email'] == $params['email']) TApiException('新邮箱不能和原邮箱相同',20006,200);
        if ($binduser) TApiException('该邮箱已被绑定到其他账号',20006,200);
        // 不存在
        // // 账号邮箱登录
        // if ($currentLoginType == 'username' || $currentLoginType == 'phone' || $currentLoginType == 'email' || $currentLoginType == 'weixin-mp' ){
        //     $user = $this->save([
        //         'email'=>$params['email']
        //     ],['id'=>$currentUserId]);
        //     // 更新缓存
        //     $currentUserInfo['email'] = $params['email'];
        //     Cache::set($currentUserInfo['token'],$currentUserInfo,config('api.token_expire'));
        //     return true;
        // }
        // 第三方登录
        if (!$currentUserId) {
            // // 在user表创建账号
            // $user = $this->create([
            //     'username'=>$params['email'],
            //     'email'=>$params['email'],
            // ]);
            // // 绑定
            // $userbind = $this->userbind()->find($currentUserInfo['id']);
            // $userbind->user_id = $user->id;
            // if ($userbind->save()) {
            //     // 更新缓存
            //     $currentUserInfo['user_id'] = $user->id;
            //     Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
            //     return true;
            // }
            TApiException();
        }
        // 直接修改
        if($this->save([
            'email'=>$params['email']
        ],['id'=>$currentUserId])) return true;
        TApiException();
    }

    // 绑定第三方登录
    public function bindother(){
        // 获取所有参数
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType,$params['provider']);
        // 查询是否存在
        $binduser = $this->isExist(['provider'=>$params['provider'],'openid'=>$params['openid']]);
        // 存在
        if ($binduser) {
            if ($binduser->user_id) TApiException('已被绑定',20006,200);
            $binduser->user_id = $currentUserId;
            return $binduser->save();
        }
        // 不存在
        return $this->userbind()->create([
            'type'=>$params['provider'],
            'openid'=>$params['openid'],
            'nickname'=>$params['nickName'],
            'avatarurl'=>$params['avatarUrl'],
            'user_id'=>$currentUserId
        ]);
    }

    //  修改头像
    public function editUserpic(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid=request()->userId;
        $image = (new Image())->upload($userid,'userpic');
        // 修改用户头像
        $user = self::get($userid);
        $user->userpic = $image->url;
        if($user->save()) return $user->userpic;
        TApiException();
    }

    //  修改背景封面
    public function editUserbgpic(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid=request()->userId;
        $image = (new Image())->upload($userid,'userbgpic');
        // 修改用户背景封面
        $user = self::get($userid);
        $user->userbgpic = $image->cloud_url;
        if($user->save()) return $user->userbgpic;
        TApiException();
    }

    // 修改资料
    public function editUserinfo(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid=request()->userId;
        // 修改昵称
        $user = $this->get($userid);
        $user->username = $params['name'];
        $user->save();
        // 修改用户信息表
        $userinfo = $user->userinfo()->find();
        $userinfo->sex = $params['sex'];
        $userinfo->age = $params['age'];
        $userinfo->qg = $params['qg'];
        $userinfo->job = $params['job'];
        $userinfo->birthday = $params['birthday'];
        $userinfo->path = $params['path'];
        $userinfo->user_resume = $params['userresume'];
        $userinfo->user_signature = $params['usersignature'];
        $userinfo->save();
        return true;
    }

    // 修改密码
    public function repassword(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $user = self::get($userid);
        // 验证用户操作
        // 手机注册的用户并没有原密码,直接修改即可
        // if ($user['password']) {
        //     // 判断旧密码是否正确
        //     $this->checkPassword($params['oldpassword'],$user['password']);
        // }
        // 修改密码
        $newpassword = password_hash($params['newpassword'],PASSWORD_DEFAULT);
        $res = $this->save([
            'password'=>$newpassword
        ],['id'=>$userid]);
        if (!$res) TApiException('修改密码失败',20009,200);
        $user['password'] = $newpassword;
        // 更新缓存信息
        Cache::set(request()->Token,$user,config('api.token_expire'));
    }

    // 关联关注
    public function withfollow(){
        return $this->hasMany('Follow','user_id');
    }
  
  // 关联粉丝（关联到follow表）
    public function withfen(){
        return $this->hasMany('Follow','follow_id');
    }

    // 关注用户
    public function ToFollow(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $user_id = request()->userId;
        $follow_id = $params['follow_id'];
        // 不能关注自己
        if($user_id == $follow_id) TApiException('非法操作',10000,200);
        // 获取到当前用户的关注模型
        $followModel = $this->get($user_id)->withfollow();
        // 查询记录是否存在
        $follow = $followModel->where('follow_id',$follow_id)->find();
        if($follow) TApiException('已经关注过了',10000,200);
        $followModel->create([
            'user_id'=>$user_id,
            'follow_id'=>$follow_id
        ]);
        return true;
    }

    // 取消关注
    public function ToUnFollow(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $user_id = request()->userId;
        $follow_id = $params['follow_id'];
        // 不能取消关注自己
        if($user_id == $follow_id) TApiException('非法操作',10000,200);
        $followModel = $this->get($user_id)->withfollow();
        $follow = $followModel->where('follow_id',$follow_id)->find();
        if(!$follow) TApiException('暂未关注',10000,200);
        return $follow->delete();
    }

    // 获取互关列表
    public function getFriendsList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $page = $params['page'];

        $subsql = \Db::table('userinfo')
        ->group('user_id')
        ->buildSql();

        $follows = \Db::table('user')->where('a.id','IN', function($query) use($userid){
            // 找出所有关注我的人的用户id
            $query->table('follow')
                ->where('user_id', 'IN', function ($query) use($userid){
                    // 找出所有我关注的人的用户id
                    $query->table('follow')->where('user_id', $userid)->field('follow_id');
                })->where('follow_id',$userid)
                ->field('user_id');
        })->alias('a')->join([$subsql=> 'w'], 'w.user_id = a.id')->page($page,10)->select();

        return $this->filterReturn($follows);
    }

    // 关联粉丝列表
    public function fens(){
        return $this->belongsToMany('User','Follow','user_id','follow_id');
    }

    // 关联关注列表
    public function follows(){
        return $this->belongsToMany('User','Follow','follow_id','user_id');
    }

    // 获取当前用户粉丝列表
    public function getFensList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $fens = $this->get($userid)->fens()->with(['userinfo'])->page($params['page'],10)->select()->toArray();
        return $this->filterReturn($fens);
    }

    // 关注和粉丝返回字段
    public function filterReturn($param = []){
        $arr = [];
        $length = count($param);
        for ($i=0; $i < $length; $i++) { 
            $arr[$i] = [
                'id'=>$param[$i]['id'],
                'username'=>$param[$i]['username'],
                'userpic'=>$param[$i]['userpic'],
            ];
            if (array_key_exists('userinfo',$param[$i])) {
                $arr[$i]['userinfo'] = $param[$i]['userinfo'];
            }
            if (array_key_exists('user_id',$param[$i])) {
                $arr[$i]['userinfo'] = [
                    'user_id'=> $param[$i]['user_id'],
                    'age' => $param[$i]['age'],
                    'sex' => $param[$i]['sex'],
                    'qg' => $param[$i]['qg'],
                    'job' => $param[$i]['job'],
                    'path' => $param[$i]['path'],
                    'birthday' => $param[$i]['birthday'],
                ];
            }
        }
        return $arr;
    }

    
    // 获取当前用户关注列表
    public function getFollowsList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $follows = $this->get($userid)->follows()->with(['userinfo'])->page($params['page'],10)->select()->toArray();
        return $this->filterReturn($follows);
    }
  
  
  // 关联评论
    public function comments(){
        return $this->hasMany('Comment');
    }

    // 关联今日文章
    public function todayPosts(){
        return $this->hasMany('Post')->whereTime('create_time','today');
    }

    // 统计获取用户相关数据（总文章数，今日文章数，评论数 ，关注数，粉丝数，文章总点赞数，好友数）
    public function getCounts(){
        // 获取用户id
       $userid = request()->param('user_id');
        $user = $this->withCount(['post','comments','todayPosts','withfollow','withfen'])->find($userid);
        if (!$user) TApiException();
        // 获取当前用户发布的所有文章id
        $postIds = $user->post()->field('id')->select();
        foreach ($postIds as $key => $value) {
            $arr[] = $value['id'];
        }
        if (!isset($arr)) $arr = 0;
        $count = \Db::name('support')->where('type',1)->where('post_id','in',$arr)->count();
      
      	// 获取好友数
        $friendCounts = \Db::table('follow')
        ->where('user_id', 'IN', function ($query) use($userid){
            // 找出所有我关注的人的用户id
            $query->table('follow')->where('user_id', $userid)->field('follow_id');
        })->where('follow_id',$userid)
        ->count();
      
        return [
            "post_count"=>$user['post_count'],
            "comments_count"=>$user['comments_count'],
            "today_posts_count"=>$user['today_posts_count'],
            "withfollow_count"=>$user['withfollow_count'],
            "withfen_count"=>$user['withfen_count'],
            "total_ding_count"=>$count,
          	"friend_count"=>$friendCounts
        ];
    }

  	// 判断当前用户userid的第三方登录绑定情况
    public function getUserBind(){
        // 获取用户id
        $userid = request()->userId;
        $userbind = $this->userbind()->where('user_id',$userid)->field('id,type,nickname')->select();
        $arr = [];
        foreach ($userbind as $key => $value) {
            $arr[$value['type']]=[
                "id" => $value['id'],
                "nickname" => $value['nickname'],
            ];
        }
        return $arr;
    }
  
  
// 获取指定用户详细信息
    public function getUserInfo(){
        $currentUserId = request()->userId ? request()->userId : 0;
        $userid = request()->param('user_id');
        $data = $this->with([
            'userinfo',
            'fens'=>function($query) use($currentUserId){
                return $query->where('user_id',$currentUserId)->hidden(['password']);
            },
          	'blacklist'=>function($query) use($currentUserId){
                return $query->where('user_id',$currentUserId)->hidden(['password']);
            },
        ])->find($userid);
        unset($data['password']);
        return $data;
    }
  
  
  // 关联黑名单
    public function blacklist(){
        return $this->belongsToMany('User','Blacklist','user_id','black_id');
    }
  
     // 逐行读取TXT文件 
     function getTxtContent($txtfile){
        $file = @fopen($txtfile,'r');
        $content = array();
        if(!$file){
            return TApiException('没有数据',30011,200);
        }else{
            $i = 0;
            while (!feof($file)){
                $content[$i] = mb_convert_encoding(fgets($file),"UTF-8","GBK,ASCII,ANSI,UTF-8");
                $i++ ;
            }
            fclose($file);
            $content = array_filter($content); //数组去空
        }
    
        return $content;
    }


    public function getCsvFileData($file)
    {
        if (!is_file($file)) {
            return TApiException('没有数据',30011,200);
        }
        $handle = fopen($file, 'r');
        if (!$handle) {
            return TApiException('读取数据失败',30012,200);
        }
        $arr = [];
        while (($data = fgetcsv($handle)) !== false) {
            // 下面这行代码可以解决中文字符乱码问题
            // $data[0] = iconv('gbk', 'utf-8', $data[0]);

            // 跳过第一行标题
            if ($data[0] == 'name') {
                continue;
            }
            // data 为每行的数据，这里转换为一维数组
            array_push($arr,$data);// 结果添加进数组
            //print_r($data);
        }
        fclose($handle);
        //print_r($arr);
        return $arr;
    }
}
