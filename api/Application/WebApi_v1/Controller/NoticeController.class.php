<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/12
 * Time: 下午 04:04
 */

namespace WebApi_v1\Controller;


class NoticeController extends UserBaseController
{
    /*
     * 我的消息详情
     * type 0-正常私信/系统消息，2-用戶評論，3-用戶回復，4-用戶點贊
     * page 当前页码 默认 1 非必填
     */
    public function myNotice() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo){
            self::returnAjax(100012);
        }

        $type               = I('type');
        $page               = I('page')? I('page') : 1;
        $num                = 6;
        if(!is_numeric($type)){
            self::returnAjax(100005);
        }

        $noticeM            = M('notice_sms');
        //私信
        if($type == 0) {
            $noticeList     = $noticeM->field('notice_id,user_id,touser_id,time,content,state')->where(array('type'=>$type,'user_id'=>0,'touser_id'=>$userInfo['user_id']))->order('time')->select();
            if($noticeList) {
                foreach ($noticeList as $k => $v) {
                    //時間轉換

                    //狀態都改爲已讀
                    /*if($v['state'] == 0) {
                        $result         = $noticeM->where(array('notice_id'=>$v['notice_id']))->save(array('state'=>1));
                        if(!$result) {
                            self::returnAjax(301);
                        }
                    }*/
                }
                self::returnAjax(200,$noticeList);
            }else{
                self::returnAjax(404);
            }
        }

        //評論消息
        if($type == 2) {
            $noticeList     = $noticeM
                            ->alias('a')
                            ->field('a.notice_id,a.touser_id,a.time,a.state,a.handle_id, b.comment_id,b.comment,b.user_id,b.push_time,b.type,b.resource_id, c.user_id,c.head_portrait,c.nickname,c.experience')
                            ->join('LEFT JOIN sex_forum_comment b ON b.comment_id = a.handle_id')
                            ->join('LEFT JOIN sex_user_list c ON c.user_id = b.user_id')
                            ->where(array('a.type'=>$type,'a.touser_id'=>$userInfo['user_id']))
                            ->order('a.time DESC')
                            ->page($page,$num)
                            ->select();
            $noticeCount    = $noticeM->where(array('type'=>$type,'touser_id'=>$userInfo['user_id']))->count();

            if($noticeList) {
                foreach ($noticeList as $k => $v) {
                    //評論用戶信息
                    //用户头像
                    $noticeList[$k]['head_portrait']    = self::ResourceUrl($v['head_portrait']);
                    //用户等级
                    $level                              = self::level($v['experience']);
                    if($level) {
                        $noticeList[$k]['level']        = $level['level']; //等级名称
                        $noticeList[$k]['icon']         = $level['icon']; //等级标志
                    }
                    //評論對象（資源或動態）
                    if($v['type'] == 0) { //動態
                        $noticeList[$k]['content']      = M('forum_dynamic')->where(array('dynamic_id'=>$v['resource_id']))->find();
                        $noticeList[$k]['content']['object']              = (array)json_decode($noticeList[$k]['content']['object']);
                        if($noticeList[$k]['content']['type'] == 1) { //視頻動態
                            //地址轉換
                            $noticeList[$k]['content']['object']['video_url']     = "/WebApi_v1/Forum/PlayForumVideo/dynamic_id/".$noticeList[$k]['content']['dynamic_id']."/index.m3u8";
                            $noticeList[$k]['content']['object']['video_img']     = self::ResourceUrl($noticeList[$k]['content']['object']['video_img']); //转换视频封面路径
                            $noticeList[$k]['content']['object']['long']          = self::MinToTime($noticeList[$k]['content']['object']['long']); //视频分钟转换为时分秒格式

                        }
                        if($noticeList[$k]['content']['type'] == 2) { //圖片動態
                            //地址轉換
                            $noticeList[$k]['content']['object']['img_url']       = self::ResourceUrl($noticeList[$k]['content']['object']['img_url']);
                        }
                    }
                    if($v['type'] == 1) { //視頻資源
                        $noticeList[$k]['content']                        = M('resource_movie')->where(array('movie_id'=>$v['resource_id']))->find();
                        $noticeList[$k]['content']['long']                = self::MinToTime($noticeList[$k]['content']['long']);
                        //地址
                        $noticeList[$k]['content']['movie_url']           = "/WebApi_v1/Movie/PlayVideo/movie_id/".$noticeList[$k]['content']['movie_id']."/index.m3u8";
                        $noticeList[$k]['content']['movie_img']           = self::ResourceUrl($noticeList[$k]['content']['movie_img']);
                    }
                    if($v['type'] == 2) { //圖片資源
                        $noticeList[$k]['content']                        = M('resource_image')->where(array('image_id'=>$v['resource_id']))->find();
                        $imageUrl                       = self::ResourceUrl($noticeList[$k]['content']['image_url']);
                        $noticeList[$k]['content']['image_url']           = json_decode($imageUrl)[0]; //只显示第一张
                    }
                    if($v['type'] == 3) { //小説資源
                        $noticeList[$k]['content']                        = M('resource_fiction')->where(array('fiction_id'=>$v['resource_id']))->find();
                        $noticeList[$k]['content']['image_url']           = self::ResourceUrl($noticeList[$k]['content']['image_url']);
                    }

                    //狀態都改爲已讀
                    if($v['state'] == 0) {
                        $result         = $noticeM->where(array('notice_id'=>$v['notice_id']))->save(array('state'=>1));
                        if(!$result) {
                            self::returnAjax(301);
                        }
                    }

                }

                self::returnAjax(200,array('pages'=>array('count'=>$noticeCount,'num'=>$num),'list'=>$noticeList));
            }else{
                self::returnAjax(404);
            }
        }

        //用戶回復
        if($type == 3) {

        }
    }
}