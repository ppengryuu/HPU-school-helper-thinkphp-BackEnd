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

// 不需要验证token
Route::group('api/:version/',function(){

    // 获取院校列表
    Route::get('app/school/list', 'api/:version.App/schoolList');

    // 给小程序点赞 支持我
    Route::post('app/supportme','api/:version.App/supportMe');
    Route::get('app/supportme','api/:version.App/supportMe');

    // 登录vpn
    Route::post('app/hpuvpn/login','api/:version.App/hpuvpnLogin');
  
    // 公告内容文件
    Route::post('app/hpuvpn/school/ann/list','api/v1.App/schoolAnnList');
    Route::get('app/hpuvpn/school/ann/list','api/v1.App/schoolAnnList');
    // 讲座列表
    Route::post('app/hpuvpn/school/lectures','api/v1.App/schoolLectures');
    // 公告列表
    Route::post('app/hpuvpn/school/ann','api/v1.App/schoolAnn');
    // 登录后勤系统
    Route::post('app/zhhq/login','api/v1.App/zhhqLogin');
    // 体测验证码
    Route::get('app/hpuvpn/tc/code','api/v1.App/tcCode');
    Route::post('app/hpuvpn/tc/code','api/v1.App/tcCode');
    // 体测查询
    Route::post('app/hpuvpn/tc/grade','api/v1.App/tcGrade');

    // 获取后勤登录session
    Route::post('app/zhhq/session','api/:version.App/zhhqSession');
    
    // 搜索空教室
    Route::post('app/freeroom/search','api/:version.App/freeroomSearch');

    // 获取空教室教学楼目录
    Route::post('app/freeroom/buildings','api/:version.App/FreeRoomBuildings');
    
    // 获取学期列表
    Route::get('app/eams/semesters','api/:version.App/getEamsSemesters');
    Route::post('app/eams/semesters','api/:version.App/getEamsSemesters');

    // 登录教务
    Route::post('app/eams/login','api/:version.App/eamsLogin');
    // 获取教务登录session
    Route::post('app/eams/session','api/:version.App/eamsSession');
    // 获取登录图书馆session    
    Route::get('app/lib/session','api/:version.App/libSession');
    Route::post('app/lib/session','api/:version.App/libSession');

    // 电费查询
    Route::post('app/dorm/electricity','api/:version.App/dormElectricity');
    // 获取 天气
    Route::get('app/weather','api/:version.App/getWeather');

    // 获取图书封面
    Route::get('app/lib/book/cover','api/:version.App/bookCover');
    
    // 获取 最新应用版本
    Route::get('app/update','api/:version.App/getLatestAppVersion');
    // 获取用户协议
    Route::get('app/appsa','api/:version.App/AppServiceAgreement');
    // 获取 用户协议 版本
    Route::get('app/config/eusaversion','api/:version.App/getEusaVersion');
    // 获取 学校地图
    Route::get('app/schoolmap','api/:version.App/getSchoolMap');
    // 获取 学校历
    Route::get('app/schoolcalender','api/:version.App/getSchoolCalender');
    // 获取 应用配置 版本
    Route::get('app/config/version','api/:version.App/getConfigVersion');
    // 获取 应用配置
    Route::get('app/config','api/:version.App/getConfig');
    // 发送验证码 手机号
    Route::post('user/sendcode','api/:version.User/sendCode');
    // 发送验证码 邮箱
    Route::post('user/sendemailcode','api/:version.User/sendEmailCode');
    // 验证码登录 手机号
    Route::post('user/phonelogin','api/:version.User/phoneLogin');
    // 验证码登录 邮箱 （暂不开放）
    // Route::post('user/emaillogin','api/:version.User/emailLogin');
    // 验证码登录 邮箱
    Route::post('user/wxmpemaillogin','api/:version.User/wxmpemailLogin');
    // 账号密码登录
    Route::post('user/login','api/:version.User/login');
    // 第三方登录
    Route::post('user/otherlogin','api/:version.User/otherLogin');
    // 获取文章分类
    Route::get('postclass', 'api/:version.PostClass/index');
    // 获取话题分类
    Route::get('topicclass','api/v1.TopicClass/index');
    // 获取热门话题
    Route::get('hottopic','api/v1.Topic/index');
    // 获取指定话题分类下的话题列表
    Route::get('topicclass/:id/topic/:page', 'api/v1.TopicClass/topic');
    // 获取文章详情
    Route::get('post/:id', 'api/v1.Post/index');
    // 获取指定话题下的文章列表
    Route::get('topic/:id/post/:page', 'api/v1.Topic/post')->middleware(['ApiGetUserid']);
    // 获取指定文章分类下的文章
    Route::get('postclass/:id/post/:page', 'api/v1.PostClass/post')->middleware(['ApiGetUserid']);
    // 获取指定用户下的文章
    Route::get('user/:id/post/:page', 'api/v1.User/post')->middleware(['ApiGetUserid']);
    // 搜索话题
    Route::post('search/topic', 'api/v1.Search/topic');
    // 搜索文章
    Route::post('search/post', 'api/v1.Search/post')->middleware(['ApiGetUserid']);
    // 搜索用户
    Route::post('search/user', 'api/v1.Search/user');
    // 广告列表
    Route::get('adsense/:type', 'api/v1.Adsense/index');
    // 获取当前文章的所有评论
    Route::get('post/:id/comment','api/v1.Post/comment');
    // 检测更新
    Route::post('update','api/v1.Update/update');
  // 获取关注的人的公开文章列表
     Route::get('followpost/:page','api/v1.Post/followPost')->middleware(['ApiGetUserid']);
  //  获取用户信息
    Route::post('getuserinfo','api/v1.User/getuserinfo')->middleware(['ApiGetUserid']);
  // 统计用户数据
    Route::get('user/getcounts/:user_id','api/v1.User/getCounts');
  // 微信小程序登录
    Route::post('wxlogin','api/v1.User/wxLogin');
    // 微信小程序2登录
    Route::post('wxlogin2','api/v1.User/wxLogin2');
  // 支付宝小程序登录
    Route::post('alilogin','api/v1.User/alilogin');
});

// 需要验证token
Route::group('api/:version/',function(){
    // 用户设置院校
    Route::post('user/school/set', 'api/:version.User/schoolSet');

    // 获取豆瓣subject_id
    Route::post('app/lib/book/detail/douban/subjectid','api/:version.App/doubanSubjectid');
    Route::get('app/lib/book/detail/douban/subjectid','api/:version.App/doubanSubjectid');
    // 单书详情
    Route::post('app/lib/book/detail','api/:version.App/bookDetail');
    // 图书馆藏情况
    Route::post('app/lib/book/collection','api/:version.App/bookCollection');
    // 查询图书馆馆藏
    Route::post('app/lib/search','api/:version.App/libSearch');


    // 修改课程表
    Route::post('/user/edittimetable','api/:version.User/editTimeTable');
    Route::post('/user/timetable/edit','api/:version.User/editTimeTable');
    // 查询空教室
    Route::get('/user/getemptyclassroom','api/:version.User/getEmptyClassroom');
    // 获取科目排名
    Route::get('user/getcourseranking','api/:version.User/getCourseRanking');
    Route::get('user/rank/subject','api/:version.User/getCourseRanking');
    // 获取前100名 传入type 获取不同类型
    Route::get('user/gettopranking','api/:version.User/getTopRanking');
    Route::get('user/rank/gpa','api/:version.User/getTopRanking');
    // 解析图书馆图书页面
    Route::post('user/parsepage','api/:version.User/ParsePage');
    // 发布校园卡寻找失主
    Route::post('user/findcard','api/:version.User/findCard');
    // 绑定教务
    Route::post('user/eams/bind','api/:version.User/eamsBind');
    // 绑定图书馆
    Route::post('user/lib/loanlist','api/:version.User/loanList');
    // 获取成绩
    Route::get('user/getgrade','api/:version.User/getGrade');
    Route::get('user/grade','api/:version.User/getGrade');
    // 获取课程表
    Route::get('user/gettimetable','api/:version.User/getTimeTable');
    Route::get('user/timetable','api/:version.User/getTimeTable');
    // 获取单日课程表
    Route::get('user/getdaytimetable','api/:version.User/getDayTimeTable');
    Route::get('user/daytimetable','api/:version.User/getDayTimeTable');
    // 获取借阅图书
    Route::get('user/getborrowbook','api/:version.User/getBorrowBook');
    Route::get('user/borrowedbooks','api/:version.User/getBorrowBook');
    // 退出登录
    Route::post('user/logout','api/:version.User/logout');
    // 绑定手机
    Route::post('user/bindphone','api/v1.User/bindphone');
    // 更换绑定手机 $$
    Route::post('user/changebindphone','api/v1.User/changebindphone');
    // 绑定邮箱
    Route::post('user/bindemail','api/v1.User/bindemail');
    // 更换绑定邮箱 $$
    Route::post('user/changebindemail','api/v1.User/changebindemail');
  // 判断当前用户第三方登录绑定情况
    Route::get('user/getuserbind','api/v1.User/getUserBind');
})->middleware(['ApiUserAuth']);

// 用户操作（绑定手机）
Route::group('api/:v1/',function(){
    // 举报用户、文章、评论 % 0 1 2(:type)
    Route::post('user/report/:type','api/:v1.User/Report');
    // 上传多图
    Route::post('image/uploadmore','api/:v1.Image/uploadMore');
    // 发布文章
    Route::post('post/create','api/v1.Post/create');
    // 获取指定用户下的所有文章（含隐私）
    Route::get('user/post/:page', 'api/v1.User/Allpost');
    // 用户获取被回复的动态
    Route::get('user/bereplyedpost/:page', 'api/v1.User/beReplyedPost');
    // 获取指定用户赞过的文章
    Route::get('user/supportedpost/:page', 'api/v1.User/supportedPost');
    // 获取指定用户参与评论的文章
    Route::get('user/commentedpost/:page', 'api/v1.User/commentedPost');
    // 删除我的动态
    Route::post('user/delmypost', 'api/v1.User/delMyPost');
    // 删除我的评论
    Route::post('user/delmycomment', 'api/v1.User/delMyComment');
    // 绑定第三方
    Route::post('user/bindother','api/v1.User/bindother');
    // 用户顶踩
    Route::post('support', 'api/v1.Support/index');
    // 用户评论
    Route::post('post/comment','api/v1.Comment/comment');
    // 编辑头像
    Route::post('edituserpic','api/v1.User/editUserpic');
    // 编辑背景
    Route::post('edituserbgpic','api/v1.User/editUserbgpic');
    // 编辑资料
    Route::post('edituserinfo','api/v1.User/editinfo');
    // 修改密码
    Route::post('repassword','api/v1.User/rePassword');
     // 加入黑名单
    Route::post('addblack','api/:v1.Blacklist/addBlack');
     // 移出黑名单
    Route::post('removeblack','api/:v1.Blacklist/removeBlack');
    // 关注
    Route::post('follow','api/v1.User/follow');
    // 取消关注
    Route::post('unfollow','api/v1.User/unfollow');
    // 互关列表
    Route::get('friends/:page','api/v1.User/friends');
    // 粉丝列表
    Route::get('fens/:page','api/v1.User/fens');
    // 关注列表
    Route::get('follows/:page','api/v1.User/follows');
    // 用户反馈
    Route::post('feedback','api/:v1.Feedback/feedback');
    // 获取用户反馈列表
    Route::get('feedbacklist/:page','api/:v1.Feedback/feedbacklist');
})->middleware(['ApiUserAuth','ApiUserBindPhone','ApiUserStatus']);


// socket 部分
Route::group('api/:v1/',function(){
    // 发送信息
    Route::post('chat/send','api/:v1.Chat/send');
    // 接收未接受信息
    Route::post('chat/get','api/:v1.Chat/get');
  // 绑定上线
    Route::post('chat/bind','api/:v1.Chat/bind');

    // 发送信息
    Route::post('message/send','api/:v1.Message/send');
    // 接收未接受信息
    Route::post('message/get','api/:v1.Message/get');
  // 绑定上线
    Route::post('message/bind','api/:v1.Message/bind');
})->middleware(['ApiUserAuth','ApiUserBindPhone','ApiUserStatus']);