<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/3
 * Time: 16:52
 */

namespace WebApi_v1\Controller;


use WebApi_v1\Common\Tool\FansToolController;
use WebApi_v1\Common\Tool\LikeToolController;

class ForumController extends UserBaseController
{
    /*
     * 社区首页
     * 参数
     * sort 排序 1、发现 2、热门
     * type 類型：1-視頻，2-圖片，3-文字 4-关注
     * page 当前页 默认 1
     */
    public function index() {

        $userInfo       = self::getUserInfo();
        $sort           = I('sort')? I('sort') : 1;
        $type           = I('type');
        $page           = I('page')? I('page') : 1;

        if($sort == 2) {
            $order      = 'a.like_count DESC,a.comment_count DESC';
        }else{
            $order      = 'a.time DESC';
        }

        $dynamicM       = M('forum_dynamic');

        if($type && $type != 4) {
            $where['a.type']    = $type; //类型
        }
        $where['a.state']       = 1; //动态审核通过
        $where['b.state']       = 1; //用户状态正常

        //社区动态
        if($type != 4) {
            $num                = 6;

            $dynamic            = $dynamicM
                                ->alias('a')
                                ->field('a.dynamic_id,a.user_id,a.type,a.time,a.like_count,a.comment_count,a.is_forward,a.forward_id,a.object, b.nickname,b.head_portrait,b.experience')
                                ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                ->where($where)
                                ->order($order)
                                ->page($page,$num)
                                ->select();
            $dynamicCount       = $dynamicM->alias('a')->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')->where($where)->count();
        }else{
            //关注动态需先登录
            if(!$userInfo) {
                self::returnAjax(100012);
            }
            $fansM              = M('forum_fans');
            $num                = 8;

            $dynamic            = $fansM
                                ->alias('a')
                                ->field('a.touser_id, b.dynamic_id,b.user_id,b.type,a.time,b.like_count,b.comment_count,b.is_forward,b.forward_id,b.object, c.nickname,c.head_portrait,c.experience')
                                ->join('LEFT JOIN sex_forum_dynamic b ON b.user_id = a.touser_id')
                                ->join('LEFT JOIN sex_user_list c ON c.user_id = a.touser_id')
                                ->where(array('a.user_id'=>$userInfo['user_id'],'c.state'=>1,'b.state'=>1))
                                ->order($order)
                                ->page($page,$num)
                                ->select();
            $dynamicCount       = $fansM->alias('a')->join('LEFT JOIN sex_forum_dynamic b ON b.user_id = a.touser_id')->join('LEFT JOIN sex_user_list c ON c.user_id = a.touser_id')->where(array('a.user_id'=>$userInfo['user_id'],'c.state'=>1,'b.state'=>1))->count();
        }


        if(!$dynamic) {
            self::returnAjax(404);
        }

        foreach ($dynamic as $k => $v) {
            //用户头像路径
            $dynamic[$k]['head_portrait']   = self::ResourceUrl($v['head_portrait']);
            //用户等级
            $level                          = self::level($v['experience']);
            if($level) {
                $dynamic[$k]['level']       = $level['level']; //等级名称
                $dynamic[$k]['icon']        = $level['icon']; //等级标志
            }
            if($type != 4) {
                //判断该动态是否是当前用户所属
                if($userInfo['user_id'] != $v['user_id']) {
                    //判断当前用户是否是此动态发布者粉丝
                    $isFans                     = self::userTouser($userInfo['user_id'],$v['user_id']);
                    if($isFans) {
                        $dynamic[$k]['isFans']  = 1; //已关注
                    }elseif(!$isFans){
                        $dynamic[$k]['isFans']  = 2; //未关注
                    }
                }else{
                    $dynamic[$k]['isFans']      = 0; //不显示该按钮
                }
            }else{
                $dynamic[$k]['isFans']          = 0; //不显示该按钮
            }

            //用户是否已点赞本条动态
            if(!$userInfo) {
                $dynamic[$k]['isLike']          = 2; //未点赞
            }else{
                $isLike                         = self::isLike($userInfo['user_id'], $v['dynamic_id']);
                if($isLike) {
                    $dynamic[$k]['isLike']      = 1; //已点赞
                }else{
                    $dynamic[$k]['isLike']      = 2; //未点赞
                }
            }

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
        }
        self::returnAjax(200,array('pages'=>array('count'=>$dynamicCount,'num'=>$num),'list'=>$dynamic));
    }

    /*
     * 关注
     * 参数
     * touser_id 被关注者id
     */
    public function fansOn() {

        $userInfo       = self::getUserInfo();

        if(!$userInfo) {
            self::returnAjax(100012); //用户未登录
        }

        $user_id            = $userInfo['user_id']; //关注者id
        $touser_id          = I('touser_id'); //关注者id

        if(!$user_id || !$touser_id) {
            self::returnAjax(100005); //参数错误
        }

        $result             = FansToolController::fans($user_id,$touser_id);

        if(!$result) {
            self::returnAjax(301);
        }

        self::returnAjax(200);
    }

    /*
     * 点赞
     * 参数
     * dynamic_id 动态id
     */
    public function likeOn() {

        $userInfo               = self::getUserInfo();

        if(!$userInfo) {
            self::returnAjax(100012); //用户未登录
        }

        $dynamic_id             = I('dynamic_id'); //动态id
        if(!$dynamic_id) {
            self::returnAjax(100005); //无效参数
        }

        $result                 = LikeToolController::like($userInfo['user_id'], $dynamic_id);

        if(!$result) {
            self::returnAjax(301); //失败
        }

        self::returnAjax(200);
    }

    /*
     * 取消点赞
     * 参数
     * user_id 用户id
     * dynamic_id 动态id
     */
    public function cancelLike() {

        $userInfo       = self::getUserInfo();

        if(!$userInfo) {
            self::returnAjax(100012); //用户未登录
        }

        $dynamic_id         = I('dynamic_id'); //动态id

        if(!$dynamic_id) {
            self::returnAjax(100005); //参数无效
        }

        $result             = LikeToolController::cancel($userInfo['user_id'],$dynamic_id);

        if(!$result) {
            self::returnAjax(301);
        }

        self::returnAjax(200);
    }

    /*
     * 社区搜索
     * keywords 模糊查询关键字
     * page 当前页 默认 1
     */
    public function forumSearch() {

        $userInfo       = self::getUserInfo();

        $keywords           = I('keywords');
        $page               = I('page')? I('page') : 1;
        $num                = 6; //每页显示动态数

        if(!$keywords) {
            self::returnAjax(100005); //参数错误
        }

        $dynamicM           = M('forum_dynamic');

        $where['a.state']   = 1;
        $where['b.state']   = 1;
        $where['b.nickname | a.object']= array("LIKE", '%' . $keywords . '%');

        $dynamic            = $dynamicM
                            ->alias('a')
                            ->field('a.dynamic_id,a.user_id,a.type,a.time,a.like_count,a.comment_count,a.is_forward,a.forward_id,a.object, b.nickname,b.head_portrait,b.experience')
                            ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                            ->where($where)
                            ->order('time DESC')
                            ->page($page,$num)
                            ->select();
        $dynamicCount       = $dynamicM->alias('a')->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')->where($where)->count();

        if(!$dynamic) {
            self::returnAjax(404);
        }

        foreach ($dynamic as $k => $v) {
            //用户头像路径
            $dynamic[$k]['head_portrait']   = self::ResourceUrl($v['head_portrait']);
            //用户等级
            $level                          = self::level($v['experience']);
            if($level) {
                $dynamic[$k]['level']       = $level['level']; //等级名称
                $dynamic[$k]['icon']        = $level['icon']; //等级标志
            }

            if(!$userInfo) {
                $dynamic[$k]['isFans']      = 0; //用户未登录不显示该按钮
                $dynamic[$k]['isLike']      = 2; //未点赞
            }else{
                //判断该动态是否是当前用户所属
                if($userInfo['user_id'] != $v['user_id']) {
                    //判断当前用户是否是此动态发布者粉丝
                    $isFans                     = self::userTouser($userInfo['user_id'],$v['user_id']);
                    if($isFans) {
                        $dynamic[$k]['isFans']  = 1; //已关注
                    }elseif(!$isFans){
                        $dynamic[$k]['isFans']  = 2; //未关注
                    }
                }else{
                    $dynamic[$k]['isFans']      = 0; //不显示该按钮
                }

                //用户是否已点赞本条动态
                $isLike                         = self::isLike($userInfo['user_id'], $v['dynamic_id']);
                if($isLike) {
                    $dynamic[$k]['isLike']      = 1; //已点赞
                }else{
                    $dynamic[$k]['isLike']      = 2; //未点赞
                }
            }

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
        }

        self::returnAjax(200, array('pages'=>array('count'=>$dynamicCount,'num'=>$num),'list'=>$dynamic));
    }

    /*
     * 社区查看图片 大图
     * dynamic_id 动态id
     */
    public function imageDetail() {

        $userInfo               = self::getUserInfo();

        $dynamic_id             = I('dynamic_id'); //动态id
        $dynamicM               = M('forum_dynamic');

        $dynamic                = $dynamicM
                                ->alias('a')
                                ->field('a.dynamic_id,a.user_id,a.object,a.like_count,a.comment_count,b.nickname,b.head_portrait')
                                ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                ->where(array('a.dynamic_id'=>$dynamic_id,'a.state'=>1))
                                ->find();

        if(!$dynamic) {
            self::returnAjax(404);
        }


        //用户头像
        $dynamic['head_portrait']   = self::ResourceUrl($dynamic['head_portrait']);

        //图片
        $dynamic['object']          = ((array)json_decode($dynamic['object']));
        $dynamic['object']['img_url'] = self::ResourceUrl($dynamic['object']['img_url']);


        //判断该动态是否是当前用户所属
        if(!$userInfo){
            $dynamic['isFans']      = 0; //不显示该按钮
        }else{
            if($userInfo['user_id'] != $dynamic['user_id']) {
                //判断当前用户是否是此动态发布者粉丝
                $isFans                     = self::userTouser($userInfo['user_id'],$dynamic['user_id']);
                if($isFans) {
                    $dynamic['isFans']  = 1; //已关注
                }elseif(!$isFans){
                    $dynamic['isFans']  = 2; //未关注
                }
            }else{
                $dynamic['isFans']      = 0; //不显示该按钮
            }
        }

        self::returnAjax(200,$dynamic);
    }

    /*
     * 社区动态详情
     * dynamic_id 动态id
     */
    public function forumDetail() {

        $userInfo       = self::getUserInfo();
        $dynamic_id     = I('dynamic_id');
        if(!$dynamic_id) {
            self::returnAjax(100005);
        }

        $dynamicM       = M('forum_dynamic');
        $dynamicInfo    = $dynamicM
                        ->alias('a')
                        ->field('a.dynamic_id,a.user_id,a.type,a.object,a.time,a.like_count,a.comment_count,a.is_forward,a.forward_id, b.head_portrait,b.nickname,b.experience')
                        ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                        ->where(array('dynamic_id'=> $dynamic_id))
                        ->find();

        if(!$dynamicInfo) {
            self::returnAjax(404);
        }

        //用户头像
        $dynamicInfo['head_portrait']   = self::ResourceUrl($dynamicInfo['head_portrait']);
        //用户等级
        $level                          = self::level($dynamicInfo['experience']);
        if($level) {
            $dynamicInfo['level']           = $level['level']; //等级名称
            $dynamicInfo['icon']            = $level['icon']; //等级标志
        }
        //判断该动态是否是当前用户所属
        if(!$userInfo) {
            $dynamicInfo['isFans']          = 0; //不显示该按钮
            $dynamicInfo['isLike']          = 2; //未点赞
        }else{
            if($userInfo['user_id'] != $dynamicInfo['user_id']) {
                //判断当前用户是否是此动态发布者粉丝
                $isFans                     = self::userTouser($userInfo['user_id'],$dynamicInfo['user_id']);
                if($isFans) {
                    $dynamicInfo['isFans']      = 1; //已关注
                }elseif(!$isFans){
                    $dynamicInfo['isFans']      = 2; //未关注
                }
            }else{
                $dynamicInfo['isFans']          = 0; //不显示该按钮
            }

            //用户是否已点赞本条动态
            $isLike                             = self::isLike($userInfo['user_id'], $dynamicInfo['dynamic_id']);
            if($isLike) {
                $dynamicInfo['isLike']          = 1; //已点赞
            }else{
                $dynamicInfo['isLike']          = 2; //未点赞
            }
        }

        //发布时间转换为几分钟前、几小时前、、、几年前
        $dynamicInfo['time']                = self::formatDate($dynamicInfo['time']);

        $dynamicInfo['object']              = ((array)json_decode($dynamicInfo['object']));
        //根据type类型 转换视频路径及图片路径
        if($dynamicInfo['type'] == 1) { //1 视频
            //视频路径

            $dynamicInfo['object']['video_img']     = self::ResourceUrl($dynamicInfo['object']['video_img']); //转换视频封面路径
            $dynamicInfo['object']['long']          = self::MinToTime($dynamicInfo['object']['long']); //视频分钟转换为时分秒格式
        }

        if($dynamicInfo['type'] == 2) { //图片
            $dynamicInfo['object']['img_url']       = self::ResourceUrl($dynamicInfo['object']['img_url']);
        }

        self::returnAjax(200,$dynamicInfo);
    }

    /*
     * 社区转发动态
     * dynamic_id 动态id
     */
    public function forward() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(10012);
        }

        $dynamic_id         = I('dynamic_id');
        if(!$dynamic_id) {
            self::returnAjax(100005);
        }

        $dynamicM           = M('forum_dynamic');
        $dynamicInfo        = $dynamicM->where(array('dynamic_id'=>$dynamic_id))->find();

        if(!$dynamicInfo) {
            self::returnAjax(301);
        }

        //构建转发数据
        $data               = array(
            'user_id'       => $userInfo['user_id'],
            'type'          => $dynamicInfo['type'],
            'object'        => $dynamicInfo['object'],
            'time'          => time(),
            'like_count'    => 0,
            'comment_count' => 0,
            'is_forward'    => 1,
            'state'         => 1
        );

        $result             = $dynamicM->add($data);
        if(!$result) {
            self::returnAjax(301);
        }

        self::returnAjax(200);
    }

    /*
     * 社区动态分享
     */


}