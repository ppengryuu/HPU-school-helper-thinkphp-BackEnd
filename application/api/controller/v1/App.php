<?php

namespace app\api\controller\v1;

use think\Db;
use think\Controller;
use think\Request;
use think\facade\Cache;
use app\common\controller\BaseController;
use app\lib\exception\BaseException;
use app\common\model\App as AppModel;

use app\common\validate\UserValidate;

class App extends BaseController
{

    // 获取院校列表
    public function schoolList(){
        $data = (new AppModel())->schoolList();
        return self::showResCode('success', $data);
    }

    // 给小程序点赞 支持我
    public function supportMe(){
        $data = (new AppModel())->supportMe();
        return self::showResCode('success', $data);
    }


    // 空教室搜索
    public function freeroomSearch(){
        $data = (new AppModel())->freeroomSearch();
        return self::showResCode('success', $data);
    }

    // 后勤session
    public function zhhqSession(){
        $data = (new AppModel())->zhhqSession();
        return self::showResCode('success', $data);
    }

    // 登录后勤系统
    public function zhhqLogin(){
        $data = (new AppModel())->zhhqLogin();
        return self::showResCode('success', $data);
    }

    // 学期列表
    public function getEamsSemesters(){
        $data = (new AppModel())->getEamsSemesters();
        return self::showResCode('success', $data);
    }

    // 空教室教学楼
    public function freeroomBuildings(){
        $data = (new AppModel())->freeroomBuildings();
        return self::showResCode('success', $data);
    }

    // 登录教务
    public function eamsLogin(){
        (new UserValidate())->goCheck('bindEAS');
        $data = (new AppModel())->eamsLogin();
        return self::showResCode('success', $data);
    }

    // 教务管理session
    public function eamsSession(){
        $data = (new AppModel())->eamsSession();
        return self::showResCode('success', $data);
    }

    // 登录学校vpn  
    public function hpuvpnLogin(){
        $data = (new AppModel())->hpuvpnLogin();
        // (new UserValidate())->goCheck('bindEAS');
        return self::showResCode('success', $data);
    }

    // 公告列表
    public function schoolAnn(){
        $data = (new AppModel())->schoolAnn();
        return self::showResCode('success', $data);
    }

    // 公告内容文件
    public function schoolAnnList(){
        $data = (new AppModel())->schoolAnnList();
        return self::showResCode('success', $data);
    }

    // 讲座列表
    public function schoolLectures(){
        $data = (new AppModel())->schoolLectures();
        return self::showResCode('success', $data);
    }

    // 体测验证码
    public function tcCode(){
        $data = (new AppModel())->tcCode();
        return self::showResCode('success', $data);
    }

    // 体测成绩
    public function tcGrade(){
        $data = (new AppModel())->tcGrade();
        return self::showResCode('success', $data);
    }

    // 查询电费
    public function dormElectricity(){
        $data = (new AppModel())->dormElectricity();
        return self::showResCode('success', $data);
    }

    //用户协议
    public function AppServiceAgreement(){
        $data = '';
        return self::showResCode('success',$data);
    }

    // 获取豆瓣subject_id
    public function doubanSubjectid(){
        $data = (new AppModel())->doubanSubjectid();
        return self::showResCode('success',$data);
    }

    // 单图书详情
    public function bookDetail(){
        $data = (new AppModel())->bookDetail();
        return self::showResCode('获取成功',$data);
    }

    // 图书封面
    public function bookCover(){
        $data = (new AppModel())->bookCover();
        return self::showResCode('获取成功',$data);
    }

    // 图书馆藏情况
    public function bookCollection(){
        $data = (new AppModel())->bookCollection();
        return self::showResCode('获取成功',$data);
    }

    // 查询图书馆馆藏
    public function libSearch(){
        $data = (new AppModel())->libSearch();
        return self::showResCode('获取成功',$data);
    }

    // 获取图书馆session
    public function libSession(){
        $data = (new AppModel())->libSession();
        return self::showResCode('获取成功',$data);
    }

    // 获取天气
    public function getWeather(){
        $data = (new AppModel())->getWeather();
        return self::showResCode('获取成功', $data);
    }

    // 获取 最新软件 版本
    public function getLatestAppVersion(){
        $data = (new AppModel())->getLatestAppVersion();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 获取 用户协议 版本
    public function getEusaVersion(){
        $data = (new AppModel())->getEusaVersion();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 获取应用配置 版本
    public function getConfigVersion(){
        $data = (new AppModel())->getConfigVersion();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }
    
    // 获取应用配置
    public function getConfig(){
        $data = (new AppModel())->getConfig();
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 获取 学校地图
    public function getSchoolMap(){
        $data = 'https://hpubox.oss-cn-shanghai.aliyuncs.com/appsource/Static/fixed/hbapp-run.png';
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }

    // 获取 学校历
    public function getSchoolCalender(){
        $data = [
        // 'https://tvax4.sinaimg.cn/large/0084cf44gy1gdtk2ym4wgj30rm1hnq90.jpg',
        // 'https://tva2.sinaimg.cn/large/0084cf44gy1ge1lae08mxj30ba0mraag.jpg',
        'https://tva3.sinaimg.cn/large/0084cf44gy1gdtk3ujv3pj30bd0mhdio.jpg',
        'https://tva1.sinaimg.cn/large/0084cf44gy1gdtk3uymnsj30bc0mgju8.jpg',
        ];
        //print_r($data);
        return self::showResCode('获取成功',$data);
    }
    
}
