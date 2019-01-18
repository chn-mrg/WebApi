<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/4
 * Time: 下午 03:11
 */

namespace WebApi_v1\Controller;


use WebApi_v1\Common\Tool\FansToolController;

class ForumUserController extends UserBaseController
{
    /*
     * 社区个人中心
     * user_id 用户id
     * page 当前页
     * type 类型 1-动态 2-粉丝 3-关注 非必填 默认 1
     */
    public function index() {

        $user               = self::getUserInfo();

        if(!$user) {
            self::returnAjax(100012);
        }

        $user_id            = I('user_id');
        $type               = I('type')? I('type') : 1;
        $page               = I('page')? I('page') : 1;

        if(!$user_id) {
            self::returnAjax(100005); //参数错误
        }

        //用户信息
        $userM              = M('user_list');
        $userInfo           = $userM->field('user_id,nickname,head_portrait,signature,experience')->where(array('user_id'=>$user_id))->find();
        if($userInfo) {
            //用户头像路径
            $userInfo['head_portrait']  = self::ResourceUrl($userInfo['head_portrait']);
            //用户等级
            $level                          = self::level($userInfo['experience']);
            if($level) {
                $userInfo['level']          = $level['level']; //等级名称
                $userInfo['icon']           = $level['icon']; //等级标志
            }
        }else{
            self::returnAjax(404);
        }

        //动态
        if($type == 1) {
            $num                = 4;
            $dynamicM           = M('forum_dynamic');
            $dynamic            = $dynamicM->field('dynamic_id,type,object,time,like_count,comment_count,is_forward,forward_id')->where(array('user_id' => $user_id))->order('time DESC')->page($page,$num)->select();
            $dynamicCount       = $dynamicM->where(array('user_id' => $user_id))->count();
            if(!$dynamic || !$dynamicCount) {
                self::returnAjax(404);
            }

            foreach ($dynamic as $k => $v) {
                //发布时间转换为几分钟前、几小时前、、、几年前
                $dynamic[$k]['time']            = self::formatDate($v['time']);
                $dynamic[$k]['object']          = ((array)json_decode($v['object']));
                //根据type类型 转换视频路径及图片路径
                if($v['type'] == 1) { //1 视频
                    //视频路径

                    $dynamic[$k]['object']['video_img']     = self::ResourceUrl($dynamic[$k]['object']['video_img']); //转换视频封面路径
                    $dynamic[$k]['object']['long']          = self::MinToTime($dynamic[$k]['object']['long']); //视频分钟转换为时分秒格式
                }

                if($v['type'] == 2) { //图片
                    $dynamic[$k]['object']['img_url']       = self::ResourceUrl($dynamic[$k]['object']['img_url']);
                }

                //是否显示删除按钮
                if($user['user_id'] == $user_id) {
                    $dynamic[$k]['isBtn']                   = 1; //显示
                }else{
                    $dynamic[$k]['isBtn']                   = 2; //不显示
                }
            }

            self::returnAjax(200, array(array('pages'=>array('count'=>$dynamicCount,'num'=>$num),'list'=>array('user'=>$userInfo,'dynamic'=>$dynamic))));
        }

        //粉丝 我是被关注者
        if($type == 2) {
            $num                = 8;
            $fansM              = M('forum_fans');
            $fans               = $fansM
                                ->alias('a')
                                ->field('a.user_id, b.nickname,b.head_portrait,b.signature,b.experience')
                                ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                ->where(array('a.touser_id'=>$user_id))
                                ->order('time DESC')
                                ->page($page,$num)
                                ->select();
            $fansCount          = $fansM->alias('a')->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')->where(array('a.touser_id'=>$user_id))->count();
            if(!$fans || !$fansCount) {
                self::returnAjax(404);
            }
            foreach ($fans as $k => $v) {
                //粉丝头像
                $fans[$k]['head_portrait']      = self::ResourceUrl($v['head_portrait']);

                //粉丝等级
                $level                          = self::level($fans[$k]['experience']);
                if($level) {
                    $fans[$k]['level']          = $level['level']; //等级名称
                    $fans[$k]['icon']           = $level['icon']; //等级标志
                }
                //用户是否与粉丝互相关注
                $isFans                     = self::userTouser($user_id,$v['user_id']);
                if($isFans) {
                    $fans[$k]['isFans']     = 1; //已关注
                }else{
                    $fans[$k]['isFans']     = 2; //未关注
                }
            }

            self::returnAjax(200, array(array('pages'=>array('count'=>$fansCount,'num'=>$num),'list'=>array('user'=>$userInfo,'fans'=>$fans))));
        }

        //用户的关注
        if($type == 3) {
            $num                = 8;
            $fansM              = M('forum_fans');
            $fans               = $fansM
                                ->alias('a')
                                ->filed('a.fans_id,a.user_id, b.nickname,b.head_portrait,b.signature,b.experience')
                                ->join('LEFT JOIN sex_user_list b ON b.user_id = a.touser_id')
                                ->where(array('a.user_id'=>$user_id))
                                ->order('time DESC')
                                ->page($page,$num)
                                ->select();
            $fansCount          = $fansM->alias('a')->join('LEFT JOIN sex_user_list b ON b.user_id = a.touser_id')->where(array('a.user_id'=>$user_id))->count();
            if(!$fans || !$fansCount) {
                self::returnAjax(404);
            }
            foreach ($fans as $k => $v) {
                //头像
                $fans['head_portrait']      = self::ResourceUrl($fans['head_portrait']);
                //用户等级
                $level                       = self::level($v['experience']);
                if($level) {
                    $fans[$k]['level']       = $level['level']; //等级名称
                    $fans[$k]['icon']        = $level['icon']; //等级标志
                }
            }

            self::returnAjax(200, array(array('pages'=>array('count'=>$fansCount,'num'=>$num),'list'=>$fans)));
        }
    }

    /*
     * 取消关注
     * fans_id 关注id
     */
    public function cancelFans() {

        $fans_id            = I('fans_id');

        if(!$fans_id) {
            self::returnAjax(100005); //无效参数
        }

        $result             = FansToolController::cancel($fans_id);

        if(!$result) {
            self::returnAjax(301);
        }

        self::returnAjax(200);
    }

    /*
     *
     * 删除动态
     * dynamic_id 动态id
     */
    public function deleteDynamic() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }

        $dynamic_id         = I('dynamic_id');
        if(!$dynamic_id) {
            self::returnAjax(100005);
        }

        $dynamicM           = M('forum_dynamic');
        $commentM           = M('forum_comment');
        $replyM             = M('forum_reply');

        //获取该动态下的所有评论id
        $comments           = $commentM->field('comment_id')->where(array('dynamic_id'=>$dynamic_id))->select();
        $result             = $dynamicM->where(array('dynamic_id'=>$dynamic_id,'user_id'=>$userInfo['user_id']))->delete(); //删除动态

        if($result) {
            $commentM->where(array('resource_id'=>$dynamic_id))->delete(); //删除评论
            $replyM->where('comment_id',array('in', $comments))->delete(); //删除回复
            self::returnAjax(200);
        }else{
            self::returnAjax(301);
        }
    }

    /*
     * 个人资料页面
     */
    public function userData() {

        $userInfo           = self::getUserInfo();

        if(!$userInfo) {
            self::returnAjax(100012);
        }

        //头像
        $userInfo['head_portrait'] = self::ResourceUrl($userInfo['head_portrait']);

        self::returnAjax(200, $userInfo);
    }

    /*
     * 修改个人资料
     * type 修改类型(1-头像 2-昵称 3-签名 4-邮箱 5-手机号)
     * content 修改内容
     */
    public function saveUser() {

        $userInfo           = self::getUserInfo();

        if(!$userInfo) {
            self::returnAjax(100012);
        }

        $type               = I('type'); //修改类型
        $content            = I('content'); //内容

        if(!$type || !$content) {
            self::returnAjax(100005);
        }

        $userM              = M('user_list');

        //修改头像
        if($type == 1) {
            $head_portrait = self::ResourceUrl($content);
            if(self::isImgUrl($head_portrait)) { //判断是否为图片
                $data['head_portrait'] = $head_portrait;
            }
        }

        //修改昵称
        if($type == 2) {
            $TemUserInfo    = $userM->where(array('nickname' => $content))->find();
            if ($TemUserInfo && $TemUserInfo['user_id'] != $userInfo['user_id']) {
                self::returnAjax(100007); //昵称重复
            }
            $data['nickname']   = $content;
        }

        //修改签名
        if($type == 3) {
            $data['signature']  = $content;
        }

        //修改邮箱
        if($type == 4) {

        }

        //修改手机
        if($type == 5) {

        }

    }

}