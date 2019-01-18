<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/3
 * Time: 20:41
 */

namespace WebApi_v1\Common\Tool;


class FansToolController
{
    /**
     * 关注事件
     * user_id 关注者id
     * touser_id 被关注者id
     */
    protected static $user_id;
    protected static $touser_id;
    protected static $fans_id;

    //关注（成为粉丝）
    public static function fans($user_id,$touser_id) {

        self::$user_id          = $user_id;
        self::$touser_id        = $touser_id;

        return self::add();
    }

    /**
     * 取消关注
     * fans_id 关注id
     */
    public function cancel($fans_id) {

        self::$fans_id          = $fans_id;

        return self::delete();
    }

    protected static function add() {

        if(!self::$user_id || !self::$touser_id) {
            return false;
            die();
        }

        //构建数据
        $data               = array(
          'user_id'         => self::$user_id,
          'touser_id'       => self::$touser_id,
          'time'            => time()
        );

        $fansM              = M('forum_fans');

        $result             = $fansM->add($data);

        if(!$result) {
            return false;
            die();
        }

        return true;
        die();
    }

    protected static function delete() {

        if(!self::$fans_id) {
            return false;
            die();
        }

        $fansM              = M('forum_fans');

        $result             = $fansM->where(array('fans_id' => self::$fans_id))->delete();

        if(!$result) {
            return false;
            die();
        }

        return true;
        die();
    }

}