<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/4
 * Time: 10:00
 */

namespace WebApi_v1\Common\Tool;


class LikeToolController
{
    /*/
     * 社区动态点赞
     */
    protected static $user_id;
    protected static $dynamic_id;
    protected static $like_id;

    /**
     * 点赞
     * user_id 用户id
     * dynamic_id 动态id
     */
    public static function like($user_id,$dynamic_id) {

        self::$user_id          = $user_id;
        self::$dynamic_id       = $dynamic_id;

        return self::add();
    }

    /**
     * 取消点赞
     * user_id 用户id
     * dynamic_id 动态id
     */
    public static function cancel($user_id,$dynamic_id) {

        self::$user_id          = $user_id;
        self::$dynamic_id       = $dynamic_id;

        return self::delete();
    }

    protected static function add() {

        if(!self::$user_id || !self::$dynamic_id) {
            return false;
            die();
        }

        //构建数据
        $data                   = array(
            'user_id'           => self::$user_id,
            'dynamic_id'        => self::$dynamic_id,
            'time'              => time()
        );

        $likeM                  = M('forum_like');

        $like_id                = $likeM->add($data);

        if(!$like_id) {
            return false;
            die();
        }

        //添加点赞消息记录
        $nociteM                = M('notice_sms');

        $touser_id              = M('forum_dynamic')->where(array('dynamic_id'=>self::$dynamic_id))->getField('user_id');
        //构建数据
        $notice_data            = array(
            'type'              => 4, //類型（0-正常私信，2-用戶評論，3-用戶回復，4-用戶點贊）
            'user_id'           => self::$user_id, //点赞用户
            'touser_id'         => $touser_id, //被点赞动态(发布此动态用户)
            'content'           => 0,
            'handle_id'         => $like_id, //点赞id
            'time'              => time(),
            'state'             => 0 //狀態：0-未讀，1-已讀
        );
        $result                 = $nociteM->add($notice_data);

        //动态点赞数自增1
        $dynamicM               = M('forum_dynamic');
        $res                    = $dynamicM->where(array('dynamic_id'=>self::$dynamic_id))->setInc('like_count');

        if(!$res || !$result) {
            return false;
            die();
        }

        return true;
        die();
    }

    protected static function delete() {

        if(!self::$user_id || !self::$dynamic_id) {
            return false;
            die();
        }

        $likeM                  = M('forum_like');
        $like_id                = $likeM->where(array('user_id'=>self::$user_id,'dynamic_id'=>self::$dynamic_id))->getField('like_id');
        $result                 = $likeM->where(array('user_id'=>self::$user_id,'dynamic_id'=>self::$dynamic_id))->delete();

        if(!$result) {
            return false;
            die();
        }

        //删除点赞消息记录
        $noticeM                = M('notice_sms');
        $delNotice              = $noticeM->where(array('type'=>4,'handle_id'=>$like_id))->delete();
        if(!$delNotice) {
            return false;
            die();
        }

        //动态点赞数自减1
        $dynamicM               = M('forum_dynamic');
        $res                    = $dynamicM->where(array('dynamic_id'=>self::$dynamic_id))->setDec('like_count');

        if(!$res) {
            return false;
            die();
        }

        return true;
        die();
    }

}