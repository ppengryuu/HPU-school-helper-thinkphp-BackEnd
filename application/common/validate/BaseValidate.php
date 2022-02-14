<?php
namespace app\common\validate;
use think\Validate;
use think\Db;
use app\lib\exception\BaseException;

class BaseValidate extends Validate
{
    public function goCheck($scene = false){
         // 获取请求传递过来的所有参数
         $params = request()->param();
         // 开始验证
         $check = $scene ?
                $this->scene($scene)->check($params):
                $this->check($params);
         if (!$check) {
            throw new BaseException(['msg'=>$this->getError(),'errorCode'=>10000,'code'=>400]);
         }
         return true;
    }

    // 判断删除的评论是当前用户发布的
    protected function isCommentOwner($value, $rule='', $data='', $field='')
    {
        // print_r($data);
        $res = Db::table('comment')->where('id',$data['commentid'])->find();
        if(!$res) return "该评论已不存在";
        // print_r($res);
        if($data['userId']!=$res['user_id']) return "你无法删除他人的评论";
        return true;
    }

    // 判断删除的动态是当前用户发布的
    protected function isPostOwner($value, $rule='', $data='', $field='')
    {
        // print_r($data);
        $res = Db::table('post')->where('id',$data['postid'])->find();
        if(!$res) return "该动态已不存在";
        // print_r($res);
        if($data['userId']!=$res['user_id']) return "你无法删除他人的动态";
        return true;
    }

    // 查找校区是否存在
    protected function isCampusExist($value, $rule='', $data='', $field='')
    {
        $res = Db::table('empty_classroom')->where('campus',$data['campus'])->find();
        if(!$res) return "该校区不存在";
        return true; 
    }

    // 查找教学楼是否存在
    protected function isBuildingExist($value, $rule='', $data='', $field='')
    {
        $res = Db::table('empty_classroom')->where('building',$data['building'])->find();
        if(!$res) return "该教学楼不存在";
        return true;
    }

    // 查找校园卡是否被绑定
    protected function cardExist($value, $rule='', $data='', $field='')
    {
        $res = Db::table('user_eams_bind')->where('jwzh',$data['cardnum'])->find();
        if(!$res) return "发布失败: 该校园卡暂时未被绑定";
        return true;
    }

    // 验证码验证 手机号
    protected function isPefectCodeP($value, $rule='', $data='', $field='')
    {
        // 验证码不存在
        $beforeCode = cache($data['phone']);
        if(!$beforeCode) return "请重新获取验证码";
        // 验证验证码
        if($value != $beforeCode) return "验证码错误";
        return true;
    }

    // 验证码验证 邮箱
    protected function isPefectCodeE($value, $rule='', $data='', $field='')
    {
        // 验证码不存在
        $beforeCode = cache($data['email']);
        if(!$beforeCode) return "请重新获取验证码";
        // 验证验证码
        if($value != $beforeCode) return "验证码错误";
        Cache::rm($data['email']); 
        return true;
    }

    // 话题是否存在
    protected function isTopicExist($value, $rule='', $data='', $field='')
    {
        if ($value==0) return true;
        if (\app\common\model\Topic::field('id')->find($value)) {
            return true;
        }
        return "该话题已不存在";
    }

    // 文章分类是否存在
    protected function isPostClassExist($value, $rule='', $data='', $field='')
    {
        if (\app\common\model\PostClass::field('id')->find($value)) {
            return true;
        }
        return "该文章分类已不存在";
    }

    // 文章是否存在
    protected function isPostExist($value, $rule='', $data='', $field='')
    {
        if (\app\common\model\Post::field('id')->find($value)) {
            return true;
        }
        return "该文章已不存在";
    }

    // 用户是否存在
    protected function isUserExist($value, $rule='', $data='', $field='')
    {
        if (\app\common\model\User::field('id')->find($value)) {
            return true;
        }
        return "该用户已不存在";
    }

    // 评论是否存在
    protected function isCommentExist($value,$rule='',$data='',$field='')
    {
        if ($value==0) return true;
        if (\app\common\model\Comment::field('id')->find($value)) return true;
        return "回复的评论已不存在";
    }

    // 不能为空
    protected function NotEmpty($value, $rule='', $data='', $field='')
    {
        if (empty($value)) return $field."不能为空";
        return true;
    }

}
