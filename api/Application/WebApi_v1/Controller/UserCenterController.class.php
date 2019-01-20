<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/8
 * Time: 下午 02:40
 */

namespace WebApi_v1\Controller;


class UserCenterController extends UserBaseController
{
    /*
     * 个人中心主页面
     */
    public function index() {

        $userInfo                       = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }

        //用户头像
        $userInfo['head_portrait']      = self::ResourceUrl($userInfo['head_portrait']);
        //等级
        $level                          = self::level($userInfo['experience']);
        $userInfo['level']              = $level['level'];
        $userInfo['icon']               = $level['icon'];

        //用户等级下一级所需经验
        $levelM                         = M('sys_level');
        $userInfo['nextExperience']     = $levelM->where(array('level'=>($userInfo['level'] +1)))->getField('experience');

        //用户今天是否已签到
        $signinM                        = M('record_signin');
        //今天的开始及结束时间戳
        $time                           = self::todayTime();
        $where['time']                  = array('between',$time);

        $isSign                         = $signinM->where($where)->count();
        if($isSign > 0) {
            $userInfo['isSign']         = 1; //已签到
        }else{
            $userInfo['isSign']         = 0; //未签到
        }

        //用户是否有未读消息
        $noticeM                        = M('notice_sms');
        $unread                         = $noticeM->where(array('touser_id'=>$userInfo['user_id'],'state'=>0))->count();
        if($unread > 0){
            $userInfo['unread']         = 1; //有未读消息
        }else{
            $userInfo['unread']         = 0; //没有新消息
        }

        self::returnAjax(200,$userInfo);
    }

    /*
     * 我的消息页面
     * page 当前页面私信页码 默认 1 非必填
     */
    public function notice() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }

        //系统消息
        $noticeM            = M('notice_sms');
        $sysNoticeCount     = $noticeM->where(array('type'=>0,'user_id'=>0,'touser_id'=>$userInfo['user_id'],'state'=>0))->count();
        if($sysNoticeCount) {
            $data['sysNoticeCount'] = $sysNoticeCount;
        }else{
            $data['sysNoticeCount'] = 0;
        }

        //回复我的
        $replyNoticeCount   = $noticeM->where(array('type'=>3,'touser_id'=>$userInfo['user_id'],'state'=>0))->count();
        if($replyNoticeCount) {
            $data['replyNoticeCount'] = $replyNoticeCount;
        }else{
            $data['replyNoticeCount'] = 0;
        }

        //评论
        $commentNoticeCount = $noticeM->where(array('type'=>2,'touser_id'=>$userInfo['user_id'],'state'=>0))->count();
        if($commentNoticeCount) {
            $data['commentNoticeCount'] = $commentNoticeCount;
        }else{
            $data['commentNoticeCount'] = 0;
        }

        //赞我的
        $likeNoticeCount = $noticeM->where(array('type'=>4,'touser_id'=>$userInfo['user_id'],'state'=>0))->count();
        if($likeNoticeCount) {
            $data['likeNoticeCount'] = $likeNoticeCount;
        }else{
            $data['likeNoticeCount'] = 0;
        }

        //交流群

        //私信
        $page               = I('page')? I('page') : 1;
        $directNotice       = $noticeM
                            ->alias('a')
                            ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                            ->where(array('a.type'=>0,'a.touser_id'=>$userInfo['user_id'],'a.state'=>0,'b.state'=>1))
                            ->order('time DESC')
                            ->group('user_id')
                            ->page($page,20)
                            ->select();
        $directNoticeCount  = $noticeM
                            ->alias('a')
                            ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                            ->where(array('a.type'=>0,'a.touser_id'=>$userInfo['user_id'],'a.state'=>0,'b.state'=>1))
                            ->count();
        if(!$directNotice || !$directNoticeCount){
            $data['directNoticeList'] = 0;
        }

        foreach ($directNotice as $k => $v) {

            //发送私信用户头像
            $directNotice[$k]['head_portrait'] = self::ResourceUrl($v['head_portrait']);
            //发送私信用户等级
            //用户等级
            $level                          = self::level($userInfo['experience']);
            if($level) {
                $directNotice[$k]['level']  = $level['level']; //等级名称
                $directNotice[$k]['icon']   = $level['icon']; //等级标志
            }
            //时间显示格式

        }

        self::returnAjax(200,array('notice'=>$data,'directNotice'=>array('pages'=>array('count'=>$directNoticeCount,'num'=>20),'list'=>$directNotice)));
    }

    /*
     * 已购资源
     */
    public function resources() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }

        $resourceM          = M('user_resource');

        //已购视频
        $movieList          = $resourceM
                            ->alias('a')
                            ->field('a.list_id,a.user_id,a.type,a.resource_id,a.pey_type,a.out_time, b.name,b.movie_url,b.movie_img,b.long')
                            ->join('LEFT JOIN sex_resource_movie b ON b.movie_id = a.resource_id')
                            ->where(array('a.type'=>1,'a.user_id'=>$userInfo['user_id']))
                            ->order('pey_time DESC')
                            ->limit(8)
                            ->select();
        $movieCount         = $resourceM
                            ->alias('a')
                            ->join('LEFT JOIN sex_resource_movie b ON b.movie_id = a.resource_id')
                            ->where(array('a.type'=>1,'a.user_id'=>$userInfo['user_id']))
                            ->count();
        if($movieList) {
            foreach ($movieList as $k => $v) {
                $movieList[$k]['movie_img'] = self::ResourceUrl($v['movie_img']); //封面图
                //时长转换
                $movieList[$k]['long']      = self::MinToTime($v['long']);
                //若为抵扣券购买，判断过期时间
                if($v['pey_type'] == 0) {
                    $time   = self::timeDiff($v['out_time'], time());
                    if($time) {
                        $movieList[$k]['time'] = $time;
                    }else{
                        $movieList[$k]['time'] = 0;
                    }
                }
            }
        }else{
            $movieList      = 0;
            $movieCount     = 0;
        }

        //已购图片
        $imgList            = $resourceM
                            ->alias('a')
                            ->field('a.list_id,a.user_id,a.type,a.resource_id,a.pey_type,a.out_time, b.name,b.image_url,b.long')
                            ->join('LEFT JOIN sex_resource_image b ON b.image_id = a.resource_id')
                            ->where(array('a.type'=>2,'a.user_id'=>$userInfo['user_id']))
                            ->order('pey_time DESC')
                            ->limit(8)
                            ->select();
        $imgCount           = $resourceM
                            ->alias('a')
                            ->join('LEFT JOIN sex_resource_image b ON b.image_id = a.resource_id')
                            ->where(array('a.type'=>2,'a.user_id'=>$userInfo['user_id']))
                            ->count();
        if($imgList) {
            foreach ($imgList as $k => $v) {
                $imgList[$k]['image_url']       = json_decode(self::ResourceUrl($v['image_url']))[0]; //封面图
                if($v['pey_type'] == 0) {
                    $time                       = self::timeDiff($v['out_time'], time());
                    if($time) {
                        $imgList[$k]['time']    = $time;
                    }else{
                        $imgList[$k]['time']    = 0;
                    }
                }
            }
        }else{
            $imgList        = 0;
            $imgCount       = 0;
        }

        //已购小说
        $fictionList        = $resourceM
                            ->alias('a')
                            ->field('a.list_id,a.user_id,a.type,a.resource_id,a.pey_type,a.out_time, b.name,b.image_url')
                            ->join('LEFT JOIN sex_resource_fiction b ON b.fiction_id = a.resource_id')
                            ->where(array('a.type'=>3,'a.user_id'=>$userInfo['user_id']))
                            ->order('pey_time DESC')
                            ->limit(8)
                            ->select();
        $fictionCount       = $resourceM
                            ->alias('a')
                            ->join('LEFT JOIN sex_resource_fiction b ON b.fiction_id = a.resource_id')
                            ->where(array('a.type'=>3,'a.user_id'=>$userInfo['user_id']))
                            ->count();
        if($fictionList) {
            foreach ($fictionList as $k => $v) {
                $fictionList[$k]['image_url']   = self::ResourceUrl($v['image_url']);
                if($v['pey_type'] == 0) {
                    $time                       = self::timeDiff($v['out_time'], time());
                    if($time) {
                        $fictionList[$k]['time']= $time;
                    }else{
                        $fictionList[$k]['time']= 0;
                    }
                }
            }
        }else{
            $fictionList    = 0;
            $fictionCount   = 0;
        }

        self::returnAjax(200, array('movie'=>array('movieList'=>$movieList,'movieCount'=>$movieCount),'image'=>array('imgList'=>$imgList,'imgCount'=>$imgCount),'fiction'=>array('fictionList'=>$fictionList,'fictionCount'=>$fictionCount)));
    }

    /*
     * 浏览记录
     * type 类型 0-社区 1-视频 2-图片 3-小说
     * page 当前页码 默认 1 非必填
     */
    public function browse() {

        $userInfo               = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(10012);
        }

        $type                   = I('type');
        $page                   = I('page')? I('page') : 1 ;
        $num                    = 8;
        if(!is_numeric($type)) {
            self::returnAjax(100005);
        }

        $browseM                = M('record_browse');
        //动态记录
        if($type == 0) {
            $browseList         = $browseM
                                ->alias('a')
                                ->field('a.record_id,a.type,a.open_time,a.resource_id, b.type,b.object')
                                ->join('LEFT JOIN sex_forum_dynamic b ON b.dynamic_id = a.resource_id')
                                ->where(array('a.type'=>0,'a.user_id'=>$userInfo['user_id']))
                                ->order('open_time DESC')
                                ->page($page,$num)
                                ->select();
            $browseCount        = $browseM
                                ->alias('a')
                                ->join('LEFT JOIN sex_forum_dynamic b ON b.dynamic_id = a.resource_id')
                                ->where(array('a.type'=>0,'a.user_id'=>$userInfo['user_id']))
                                ->count();

            if(!$browseCount) {
                self::returnAjax(404);
            }
            foreach ($browseList as $k => $v) {
                //查看时间
                $browseList[$k]['open_time']                    = date('Y-m-d H:i:s', $v['open_time']);
                //地址转换
                $browseList[$k]['object']                       = ((array)json_decode($v['object']));
                //根据type类型 转换视频路径及图片路径
                if($v['type'] == 1) { //1 视频
                    //视频路径

                    $browseList[$k]['object']['video_img']     = self::ResourceUrl($browseList[$k]['object']['video_img']); //转换视频封面路径
                    $browseList[$k]['object']['long']          = self::MinToTime($browseList[$k]['object']['long']); //视频分钟转换为时分秒格式
                }

                if($v['type'] == 2) { //图片
                    $browseList[$k]['object']['img_url']       = self::ResourceUrl($browseList[$k]['object']['img_url']);
                }
            }

        }
        //视频记录
        if($type == 1) {
            $browseList         = $browseM
                                ->alias('a')
                                ->field('a.record_id,a.type,a.open_time,a.resource_id, b.name,b.long,b.movie_img')
                                ->join('LEFT JOIN sex_resource_movie b ON b.movie_id = a.resource_id')
                                ->where(array('a.type'=>1,'a.user_id'=>$userInfo['user_id']))
                                ->order('open_time DESC')
                                ->page($page,$num)
                                ->select();
            $browseCount        = $browseM->alias('a')->join('LEFT JOIN sex_resource_movie b ON b.movie_id = a.resource_id')->where(array('a.type'=>1,'a.user_id'=>$userInfo['user_id']))->count();

            if(!$browseList) {
                self::returnAjax(404);
            }
            foreach ($browseList as $k => $v) {
                //查看时间
                $browseList[$k]['open_time']        = date('Y-m-d H:i:s', $v['open_time']);
                //视频地址
                //视频封面图
                $browseList[$k]['movie_img']        = self::ResourceUrl($v['movie_img']);
                //视频时长
                $browseList[$k]['long']             = self::MinToTime($v['long']);
            }
        }

        //图片记录
        if($type == 2) {
            $browseList         = $browseM
                                ->alias('a')
                                ->field('a.record_id,a.type,a.open_time,a.resource_id, b.name,b.long,b.image_url')
                                ->join('LEFT JOIN sex_resource_image b ON b.image_id = a.resource_id')
                                ->where(array('a.type'=>2,'a.user_id'=>$userInfo['user_id']))
                                ->order('open_time DESC')
                                ->page($page,$num)
                                ->select();
            $browseCount        = $browseM->alias('a')->join('LEFT JOIN sex_resource_image b ON b.image_id = a.resource_id')->where(array('a.type'=>2,'a.user_id'=>$userInfo['user_id']))->count();

            if(!$browseList){
                self::returnAjax(404);
            }
            foreach ($browseList as $k => $v) {
                //查看时间
                $browseList[$k]['open_time']        = date('Y-m-d H:i:s', $v['open_time']);
                //图集封面图
                $browseList[$k]['image_url']        = json_decode(self::ResourceUrl($v['image_url']))[0];
            }
        }

        //小说记录
        if($type == 3) {
            $browseList         = $browseM
                                ->alias('a')
                                ->field('a.record_id,a.type,a.open_time,a.resource_id, b.name,b.image_url')
                                ->join('LEFT JOIN sex_resource_fiction b ON b.fiction_id = a.resource_id')
                                ->where(array('a.type'=>3,'a.user_id'=>$userInfo['user_id']))
                                ->order('open_time DESC')
                                ->page($page,$num)
                                ->select();
            $browseCount        = $browseM->alias('a')->join('LEFT JOIN sex_resource_fiction b ON b.fiction_id = a.resource_id')->where(array('a.type'=>3,'a.user_id'=>$userInfo['user_id']))->count();

            if(!$browseList){
                self::returnAjax(404);
            }
            foreach ($browseList as $k => $v) {
                //查看时间
                $browseList[$k]['open_time']        = date('Y-m-d H:i:s', $v['open_time']);
                //图集封面图
                $browseList[$k]['image_url']        = self::ResourceUrl($v['image_url']);
            }
        }

        self::returnAjax(200,array('pages'=>array('count'=>$browseCount,'num'=>$num),'list'=>$browseList));

    }

    /*
     * 获取用户信息
     */
    public function userInfo() {

        $userInfo       = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }

        //用户头像
        $userInfo['head_portrait']  = self::ResourceUrl($userInfo['head_portrait']);

        self::returnAjax(200, $userInfo);
    }
}