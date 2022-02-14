<?php

namespace app\common\model;
use app\lib\exception\BaseException;

use think\Db;
use think\Model;
use think\facade\Cache;

class App extends Model
{

    // 获取应用配置
    public function getConfig()
    {
        $data = config()['appconfig'];
        return $data;
    }

    // 获取应用配置 版本
    public function getConfigVersion()
    {
        $data = config('appconfig.version');
        return $data;
    }

    // 获取 用户协议 版本
    public function getEusaVersion()
    {
        $data = config('appconfig.eusa_version');
        return $data;
    }

    // 获取 最新软件 版本
    public function getLatestAppVersion()
    {
        $data['ver'] = config('appconfig.app_version');
        $data['url'] = config('appconfig.app_url');
        return $data;
    }

    // 获取院校列表
    public function schoolList(){
        $data = Db::table('school')->where('status', 1)->select();
        return $data;
    }


    public function formatNumber($num){
        return $num >= 1e4?round(($num / 1e4), 2).'w':$num.'';
        return ($num>=1e3&&$num<1e4)?round(($num / 1e3), 2).'k':($num >= 1e4?round(($num / 1e4), 2).'w':$num.'');
    }

    public function txapiSentence(){

        /* 注释本行开启php请求方式
        $res = httpWurl(
            "http://api.tianapi.com/txapi/everyday/index",
            [
                "key" => "c3d8c0bbd000cad41e550ee97d00bb59",
                "rand" => 1
            ]
        );
        $res = json_decode($res, true);
        $db = Db::connect(config('sysconfig.sys_database'))->table('txapi_sentence');  
        if($res['code'] == 200){
            $data = $res['newslist'];
            foreach ($data as $item) {
                $fet = $db->where('tid', $item['id'])->find();
                if(!$fet){
                    $item['tid'] = $item['id'];
                    unset($item['id']);
                    $item['create_time'] = time();
                    $db->insert($item);
                } 
            }
        }
        // */

        $db = Db::connect(config('sysconfig.sys_database'))->table('txapi_sentence'); 
        // $result = $db->query("SELECT * FROM txapi_sentence WHERE id >= ((SELECT MAX(id) FROM txapi_sentence)-(SELECT MIN(id) FROM txapi_sentence)) * RAND() + (SELECT MIN(id) FROM txapi_sentence) LIMIT 1");
        // $result = $db->query("SELECT * FROM txapi_sentence AS r1 JOIN (SELECT CEIL(RAND() * (SELECT MAX(id) FROM txapi_sentence)) AS id) AS r2 WHERE r1.id >= r2.id ORDER BY r1.id ASC LIMIT 1");
        $result = $db->orderRaw('rand()')->limit(1)->find();
        // $result->setInc('takes');
        Db::connect(config('sysconfig.sys_database'))
        ->table('txapi_sentence')
        ->where('id', $result['id'])
        ->setInc('takes');
        // print_r($result);
        // $result = $result[0];
        // if(!chkurl($result['imgurl'])) $result['imgurl'] = "";
        $imgstyle = 'width: 100%;height: auto;clip-path: inset(80px 0 0 0);margin: -60px 0 20px 0;';
        $htmld = "<img src='".$result['imgurl']."' style='".$imgstyle."'></img>"
                .'<div>'.$result['note'].'</div>'
                .'<div>'.$result['content'].'</div>';
        return $htmld;

    }

    public function aBeautifulSentence(){
        return $this->txapiSentence();
        $sentences = [
            '你今天真好看！',
            '祝你每天都有好心情',
            '屏幕面前是哪个小可爱呢？',
            '祝你天天开心！',
            '收到你的赞了，我也要给你点个赞！'
        ];
        return '<div>'.$sentences[mt_rand(0, count($sentences)-1)].'</div>';
    }

    // 给小程序点赞 支持我
    public function supportMe(){
        header('Cache-control: must-revalidate, max-age=0, no-cache, no-store');    // HTTP/1.1设定本页面不缓存
        header("Pragma: no-cache");                                                 // HTTP/1.0 设定本页面不缓存
        $params = request()->param();
        if(!array_key_exists("page_id", $params)) TApiException('未知的页面参数',30025,200);
        if(!array_key_exists("type", $params)) TApiException('未知类型',30025,200);
        $data = 0;
        if ($params['type'] == 0) {
            $data = Db::table('app_support')->where('page_id', $params['page_id'])->find();
        }
        if ($params['type'] == 1) {
            $db = Db::table('app_support')->where('page_id', $params['page_id']);
            $db->setInc('num');
            $data = $db->find();
            $data['description'] = $data['description'].$this->aBeautifulSentence(); // array_key_exists("test", $params)?$data['description'].$this->aBeautifulSentence():$data['description'];
            if(array_key_exists("test", $params)){
                $data['description'] = $this->aBeautifulSentence();
            } 
        }
        if ($data) {
            $data['num'] = $this->formatNumber($data['num']);
            return $data;
        } else {
            TApiException('操作失败',30025,200);
        }
    }

    // 学期列表 从数据库返回数据
    public function getEamsSemesters(){
        $db_res = Db::table('semester_data')->select();
        $default_index = 0;
        for ($dri=0; $dri < count($db_res); $dri++) { 
            if ($db_res[$dri]['semester_id'] == config('appconfig.semester')['id']) {
                $default_index = $dri;
            }
        }
        $data['list'] = $db_res;
        $data['defaultSem'] = [
            'index' => $default_index
        ];
        return $data;
    }


    // 学期列表 只请求api 不返回给用户
    public function eamsSemesters(){
        $params = request()->param();
        $clientip = request()->ip();

        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        $currentUserId = $params["user_id"];

        $db = Db::connect(config('sysconfig.sys_database'));     
        $session = $db->table('eams_session')->where('user_id', $currentUserId)->value('content');

        // halt($session.$currentUserId);
        $res = hshaApi(
            '/eams/semesters',
            [ 
                'session' => $session,
                'ip' => $clientip
            ]
        );

        return $res['data'];

    }

    // 获取图书馆session
    public function libSession(){
        $params = request()->param();
        $clientip = request()->ip();

        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        $uid = $params["user_id"];

        $res = hshaApi(
            '/lib/session',
            [ 'ip' => $clientip ]
        );

        // return $res;

        $db = Db::connect(config('sysconfig.sys_database'));
        $db->table('library_session')->where('user_id', $uid)->delete();
        $nsid = $db->table('library_session')->insertGetId([
            'user_id' => $uid,
            'content' => $res['session'],
            'code_key' => $res['codeKey'],
            'captcha' => $res['verifyCode'],
            'create_time' => time()
        ]);

        $data["session_id"] = $nsid;
        $data["captcha"] = $res['verifyCode'];

        return $data;
        
    }

    // 图书封面
    public function bookCover(){
        $params = request()->param();
        
        if(!array_key_exists("title", $params)) TApiException('请输入图书名',30027,200);
        if(!array_key_exists("isbn", $params)) TApiException('请输入ISBN',30027,200);

        $res = httpWurl(
            "https://mfindhpu.libsp.com/find/book/getDuxiuImageUrl", 
            $params, 
            'GET', 
            [ 'User-Agent' => 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/7.0.14(0x17000e2e) NetType/4G Language/zh_CN' ]
        );

        $res = json_decode($res, true);
        if(!$res['success']) TApiException($res['message'],30027,200);

        return $res['data'];
    }

    // 图书馆馆藏搜索 - 简单搜索
    public function libSearch(){
        $params = request()->param();
        $clientip = request()->ip();

        if(!array_key_exists("keyword", $params)) TApiException('请输入关键词',30027,200);
        if(!array_key_exists("page", $params)) TApiException('请输入页码',30027,200);
        // TApiException('test1',30027,200);
        $keyword = $params['keyword'];
        $page = $params['page'];
        // TApiException($clientip,30027,200);
        
        $res = hshaApi(
            '/lib/book/simpleSearch',
            [ 
                'keyword' => $params['keyword'],
                'page' => $params['page'],
                'ip' => $clientip
            ]
        );
        // print_r($res);
        return $res['data'];
    }

    // 图书详情
    public function bookDetail(){
        $params = request()->param();
        $clientip = request()->ip();
        if(!array_key_exists("record_id", $params)) TApiException('请选择图书',30027,200);
        $id = $params['record_id'];

        $res = hshaApi(
            '/lib/book/detail',
            [ 
                'recordId' => $id,
                'ip' => $clientip
            ]
        );

        return $res['data'];
        
    }

    // 图书馆藏信息
    public function bookCollection(){
        $params = request()->param();
        $clientip = request()->ip();
        if(!array_key_exists("record_id", $params)) TApiException('请选择图书',30027,200);
        $id = $params['record_id'];

        $res = hshaApi(
            '/lib/book/collection',
            [ 
                'recordId' => $id,
                'ip' => $clientip
            ]
        );

        return $res['data'];
        
    }

    // 获取图书豆瓣subjectid
    public function doubanSubjectid(){
        $params = request()->param();
        if(!array_key_exists("isbn", $params)) TApiException('isbn不能为空',30027,200);
        $isbn = $params['isbn'];
        $f_url = get_redirect_url('http://douban.com/isbn/'.$isbn.'/');
        $pattern = '/-?[1-9]\d*/';
        preg_match($pattern,$f_url,$match);
        if(!$match) TApiException('豆瓣暂未收录该书籍',30023,200);
        if($match[0] === $isbn) TApiException('豆瓣暂未收录该书籍',30023,200);

        return $match[0];
    }

    // 获取空教室教学楼
    public function freeroomBuildings(){
        $params = request()->param();
        
        $clientip = request()->ip();
        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        $currentUserId = $params["user_id"];

        $db = Db::connect(config('sysconfig.sys_database'));     
        $session = $db->table('eams_session')->where('user_id', $currentUserId)->value('content');

        // halt($session);
        $res = hshaApi(
            '/eams/freeroom/buildings',
            [ 'session' => $session ],
            false
        );

        if (array_key_exists('error_code', $res)&&$res['error_code']==10003) return false;

        $data["data"] = $res['data'];
        return $data;
    }

    // 空教室搜索
    public function freeroomSearch(){
        $params = request()->param();
        $clientip = request()->ip();
        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30027,200);
        if(!array_key_exists("building_id", $params)) TApiException('未指定教学楼！',30027,200);
        if(!array_key_exists("date_begin", $params)) TApiException('未指定开始日期',30027,200);
        if(!array_key_exists("date_end", $params)) TApiException('未指定结束日期！',30027,200);
        if(!array_key_exists("sections", $params)) TApiException('未指定节次！',30027,200);
        if(!array_key_exists("mode", $params)) TApiException('未指定模式！',30027,200);
        $currentUserId = $params["user_id"];

        $storKey = $params;
        unset($storKey['user_id']);
        $storKey = 'freeroom_search_'.implode('_',$storKey);
        $cacheData = cache($storKey);
        // print_r($cacheData);
        if($cacheData){
            $data["isCache"] = true;
            $data['data'] = $cacheData;
            return $data;
        }

        $db = Db::connect(config('sysconfig.sys_database'));     
        $session = $db->table('eams_session')->where('user_id', $currentUserId)->value('content');

        $res = hshaApi(
            '/eams/freeroom/search',
            [ 
                'session' => $session,
                'buildingId' => $params['building_id'],
                'dateBegin' => $params['date_begin'],
                'dateEnd' => $params['date_end'],
                'section' => $params['sections'],
                'mode' => $params['mode'] ,
                'ip' => $clientip
            ],
            false
        );


        if(array_key_exists('error_code', $res)&&$res['error_code']==10004) TApiException($res['msg'],30029,200);
        
        Cache::set($storKey, $res['data'], 60*60*24);
        $data['isCache'] = false;
        $data["data"] = $res['data'];

        return $data;
        
    }

    // 获取教务session
    public function eamsSession(){
        $params = request()->param();
        $clientip = request()->ip();

        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        $uid = $params["user_id"];

        $res = hshaApi(
            '/eams/session',
            [ 'ip' => $clientip ]
        );

        // halt(time());

        $db = Db::connect(config('sysconfig.sys_database'));
        $db->table('eams_session')->where('user_id', $uid)->delete();
        $nsid = $db->table('eams_session')->insertGetId([
            'user_id' => $uid,
            'content' => $res['content'],
            'sha_header' => $res['sha1H'],
            'aes_header' => $res['aesH'],
            'captcha' => $res['captcha'],
            'create_time' => time()
        ]);

        $data["session_id"] = $nsid;
        $data["captcha"] = $res['captcha'];

        return $data;
    }

    // 登录教务管理系统
    public function eamsLogin(){
        $clientip = request()->ip();
        $params = request()->param();
        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        $currentUserId = $params["user_id"];

        $db = Db::connect(config('sysconfig.sys_database'));     
        $datab_res = $db->table('eams_session')->where('user_id', $currentUserId)->find();
        $session = $datab_res['content'];       #   ->value('content');
        $sha1H = $datab_res['sha_header'];         #   ->value('sha_header');
        $aesH = $datab_res['aes_header'];          #   ->value('aes_header');

        $res = hshaApi(
            '/eams/login',
            [ 
                'ip' => $clientip ,
                'username' => $params['jwzh'],
                'password' => $params['jwmm'],
                'captcha' => $params['captcha'],
                'session' => $session,
                'sha1H' => $sha1H,
                'aesH' => $aesH
            ]
        );

        // return $res;
        $new_session = $res['session'];

        $db->table('eams_session')->where('user_id', $currentUserId)->update(['content' => $new_session]);

        $cacheData = cache("semesterData_all");
        if(!$cacheData){
            $sa_res = $this->eamsSemesters();
            // halt($sa_res);
            Db::name('semester_data')->delete(true);
            for ($sai=0; $sai < count($sa_res); $sai++) { 
                Db::table('semester_data')->insert($sa_res[$sai]);
            }
            Cache::set("semesterData_all", $sa_res, 60*60*24*30);
        }

        $data = $new_session;
        return $data;
        
    }

    // 报修session
    public function zhhqSession(){
        $params = request()->param();
        $clientip = request()->ip();

        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        $uid = $params["user_id"];

        $res = hshaApi(
            '/zhhq/session',
            [ 'ip' => $clientip ]
        );

        // return $res;

        $db = Db::connect(config('sysconfig.sys_database'));
        $db->table('zhhq_session')->where('user_id', $uid)->delete();
        $nsid = $db->table('zhhq_session')->insertGetId([
            'user_id' => $uid,
            'content' => $res['content'],
            'execution' => $res['execution'],
            'lt' => $res['lt'],
            'token' => $res['token'],
            'captcha' => $res['captcha'],
            'create_time' => time()
        ]);

        // return $res;
        
        $data["session_id"] = $nsid;
        $data["captcha"] = $res["captcha"];

        return $data;
    }

    // 登录 报修
    public function zhhqLogin(){
        $params = request()->param();
        $clientip = request()->ip();
        if(!array_key_exists("usernum", $params)) TApiException('账号不能为空',30031,200);
        if(!array_key_exists("userpwd", $params)) TApiException('密码不能为空',30031,200);
        if(!array_key_exists("user_id", $params)) TApiException('请先登录',30031,200);
        if(!array_key_exists("captcha", $params)) TApiException('验证码不能为空',30031,200);

        $currentUserId = $params['user_id'];

        $db = Db::connect(config('sysconfig.sys_database'));     
        $db_res = $db->table('zhhq_session')->where('user_id', $currentUserId)->find();

        // print_r($db_res);

        $res = hshaApi(
            '/zhhq/login',
            [ 
                'username' => $params['usernum'] ,
                'password' => $params['userpwd'] ,
                'session' =>  $db_res['content'] ,
                'lt' => $db_res['lt'] ,
                'execution' => $db_res['execution'] ,
                'token' => $db_res['token'] ,
                'captcha' => $params['captcha'] ,
                'ip' => $clientip 
            ]
        );

        $n_session = json_decode(urldecode($res['session']), true);
        // halt($n_session);

        $o_session = '';
        foreach($n_session as $key => $value){
            $o_session = $o_session.$key.'='.$value.';';
        }

        $res['session'] = $o_session;

        $data['data'] = $res;

        return $data;

    }

    // 登录hpuvpn 校外访问
    public function hpuvpnLogin(){
        $clientip = request()->ip();
        $params = request()->param();
        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        if(!array_key_exists("jwzh", $params)) TApiException('账号不能为空',30031,200);
        if(!array_key_exists("jwmm", $params)) TApiException('密码不能为空',30031,200);

        $currentUserId = $params["user_id"];

        $res = hshaApi(
            '/hpuvpn/login',
            [ 
                'jwzh' => $params['jwzh'] ,
                'jwmm' => $params['jwmm'] ,
                'ip' => $clientip 
            ]
        );

        $db = Db::connect(config('sysconfig.sys_database'));  
        $db->table('hpuvpn_session')->where('user_id', $currentUserId)->delete();
        $ins_res = $db->table('hpuvpn_session')->insertGetId([
            'user_id' => $currentUserId,
            'content' => $res['session'],
            'create_time' => time()
        ]);

        $data["res"] = $ins_res;

        return $data;
    }

    // 公告列表
    public function schoolAnn(){
        $params = request()->param();
        $clientip = request()->ip();

        $page = array_key_exists("page", $params)?$params["page"]:0;
        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        $uid = $params["user_id"];


        $db = Db::connect(config('sysconfig.sys_database'));     
        $session = $db->table('hpuvpn_session')->where('user_id', $uid)->value('content');

        $res = hshaApi(
            '/hpuvpn/school/ann',
            [ 
                'session' => $session,
                'page' => $page ,
                'ip' => $clientip

            ],
            false
        );

        if(array_key_exists('error_code', $res)){
            if($res['error_code']==30008){
                TApiException('讲座: 没有数据',999,200);
            } else {
                TApiException($res['msg'],30029,200);
            }
        }

        return $res['data'];
        
    }

    // 公告文件列表
    public function schoolAnnList(){
        $params = request()->param();
        if(!array_key_exists("id", $params)) TApiException('请选择要查看的公告',999,200);
        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        $uid = $params["user_id"];
        $id = $params["id"];

        $db = Db::connect(config('sysconfig.sys_database'));     
        $session = $db->table('hpuvpn_session')->where('user_id', $uid)->value('content');

        $res = hshaApi(
            '/hpuvpn/school/ann/list',
            [ 
                'session' => $session,
                'annid' => $id 
            ]
        );

        return $res;
        
    }

    // 讲座列表
    public function schoolLectures(){
        $params = request()->param();
        $clientip = request()->ip();
        $page = array_key_exists("page", $params)?$params["page"]:1;
        
        if(!array_key_exists("user_id", $params)) TApiException('请先登录！',30025,200);
        $uid = $params["user_id"];

        $db = Db::connect(config('sysconfig.sys_database'));     
        $session = $db->table('hpuvpn_session')->where('user_id', $uid)->value('content');

        $res = hshaApi(
            '/hpuvpn/school/lectures',
            [ 
                'session' => $session,
                'page' => $page ,
                'ip' => $clientip
            ],
            false
        );

        if(array_key_exists('error_code', $res)){
            if($res['error_code']==30007){
                TApiException('讲座: 没有数据',999,200);
            } else {
                TApiException($res['msg'],30029,200);
            }
        }

        return $res['data'];
        
    }

    // 体测验证码

    public function tcCode(){
        $clientip = request()->ip();
        $params = request()->param();

        if(!array_key_exists("user_id", $params)) TApiException('请先登录',30031,200);
        $uid = $params["user_id"];

        $db = Db::connect(config('sysconfig.sys_database'));     
        $session = $db->table('hpuvpn_session')->where('user_id', $uid)->value('content');

        $res = hshaApi(
            '/hpuvpn/tc/code',
            [ 
                'session' => $session,
                'ip' => $clientip

            ],
            $ahe = false
        );

        if(array_key_exists('error_code', $res)){
            if($res['error_code']==30002){
                return false;
            } else {
                TApiException($res['msg'],30029,200);
            }
        }
        
        $db->table('hpuvpn_session')->where('user_id', $uid)->delete();
        $ins_res = $db->table('hpuvpn_session')->insertGetId([
            'user_id' => $uid,
            'content' => $res['session'],
            'create_time' => time()
        ]);

        return $res['captcha'];

    }

    // 体测成绩
    public function tcGrade(){
        $params = request()->param();
        $clientip = request()->ip();

        if(!array_key_exists("user_id", $params)) TApiException('请先登录',30031,200);
        $usernum = array_key_exists("usernum", $params)?$params["usernum"]:"";
        $userpwd = array_key_exists("userpwd", $params)?$params["userpwd"]:"";
        $captcha = array_key_exists("captcha", $params)?$params["captcha"]:"";
        if(!strlen($usernum)||!strlen($userpwd)||!strlen($captcha)) TApiException('错误',30024,200);

        $uid = $params["user_id"];
        
        $db = Db::connect(config('sysconfig.sys_database'));     
        $session = $db->table('hpuvpn_session')->where('user_id', $uid)->value('content');

        $res = hshaApi(
            '/hpuvpn/tc/grade',
            [ 
                'session' => $session,
                'usernum' => $usernum,
                'userpwd' => $userpwd,
                'captcha' => $captcha,
                'ip' => $clientip

            ]
        );

        return $res['data'];

    }

    // 电费查询
    public function dormElectricity(){

        $params = request()->param();
        // halt(request());
        $lou = array_key_exists("lou", $params)?$params["lou"]:"";
        $ceng = array_key_exists("ceng", $params)?$params["ceng"]:"";
        $room = array_key_exists("room", $params)?$params["room"]:"";
        // $cacheData = cache("electricData_".$lou."_".$ceng."_".$room);
        // if($cacheData){
        //     // $cacheData['isCache'] = true; 
        //     return $cacheData;
        // }


        $res = hshaApi(
            '/dorm/electricity',
            [ 
                'lou' => $lou,
                'ceng' => $ceng,
                'room' => $room
            ],
            true,
            'POST',
            'http://localhost:5022'
        );

        // $res['data']['isCache'] = false; 
        // Cache::set("electricData_".$lou."_".$ceng."_".$room, $res['data'], 12);
        return $res['data'];
        
    }

    // 获取天气
    public function getWeather(){
        $params = request()->param();
        $cacheArr = cache("weatherData");
        if($cacheArr){
            $data = $cacheArr;
            $data["isCache"] = true;
            return $data;
        }
        $header = [ 
            'Expect:',
            'User-Agent' => 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/7.0.14(0x17000e2e) NetType/4G Language/zh_CN'
        ];
        $url = 'http://api.map.baidu.com/weather/v1/';
        // http://api.map.baidu.com/weather/v1/?district_id=222405&data_type=all&ak=你的ak
        // if(!array_key_exists('district_id', $params)) TApiException();
        $data['district_id'] = "410811";
        $data['data_type'] = 'all';
        $data['ak'] = 'X17oAyxLEmXAMWVBXMthihsug9onlKYw';
        $data['output'] = 'json';
        $arr = httpWurl($url, $data, 'GET', $header);
        $res = json_decode($arr, true);
        
        // /* 获取pm2.5、天气描述
        // $cookie=dirname(__FILE__)."/bdw_cookie.txt"; 

        /* api v3 不可用时开启注释

        $data2["ak"] = "mT60BZbfwEgGzydmL0zbBthK2zIuofA7";
        $data2["coord_type"] = "gcj02";
        $data2["location"] = "113.2722052100,35.1943326600";
        $data2["output"] = "json";
        $data2["sn"] = "";
        $data2["timestamp"] = "";
        $arr2 = httpWurl(
            "https://api.map.baidu.com/telematics/v3/weather", 
            $data2, 
            'POST', 
            $header,
            "https://servicewechat.com/wx2dda51b428f2411f/0/page-frame.html"
        );
        // print_r($arr2);
        $res2 = json_decode($arr2, true);

        // print_r($res2);

        if($res["status"] == 0) {
            $res["result"]["now"]["pm25"] = false;
        } else {
            $res["result"]["now"]["pm25"] = $res2["results"][0]["pm25"];
        }

        try {
            $res2["results"][0]["index"];
        } catch (\Throwable $th) {
            Cache::set("weatherData", $res, 1800);
            $res["isCache"] = false;
            return $res;
        }

        array_splice($res2["results"][0]["index"], 1, 1);
        $res["result"]["now"]["describes"] = $res2["results"][0]["index"];

        // unset(($res2["results"][0]["index"])[1]);
        // api v3 不可用时注释 */

        Cache::set("weatherData", $res, 1800);
        $res["isCache"] = false;
        return $res;
    }


}
