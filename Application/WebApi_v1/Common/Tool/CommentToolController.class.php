<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/2
 * Time: 21:13
 */

namespace WebApi_v1\Common\Tool;


class CommentToolController
{
    /**
     * 评论(回复)事件
     */
    protected static $user_id; //用户id
    protected static $type; //类型
    protected static $resource_id; //资源id
    protected static $comment; //评论内容
    protected static $parent_id; //回复父级id
    protected static $comment_id; //评论id
    protected static $reply; //回复内容

    /*
     * 评论
     * user_id 用户id
     * type 资源类型（0-社區動態，1-視頻，2-圖片，3-小説）
     * resource_id 资源id
     * comment 评论内容
     */
    public static function comment($user_id,$type,$resource_id,$comment) {

        self::$user_id          = $user_id;
        self::$type             = $type;
        self::$resource_id      = $resource_id;
        self::$comment          = $comment;

        return self::addComment();
    }

    /*
     * 回复
     * 参数
     * user_id 用户id
     * parent_id 非必填 默认为0 （为0时是回复评论）
     * comment_id 评论id
     * reply 回复内容
     */
    public static function reply($user_id,$parent_id,$comment_id,$reply) {

        self::$user_id          = $user_id;
        self::$parent_id        = $parent_id;
        self::$comment_id       = $comment_id;
        self::$reply            = $reply;

        return self::addReply();
    }

    protected static function addComment() {

        if(!self::$user_id || !self::$resource_id || !self::$comment || !is_numeric(self::$type) ) {
            return false;
            die();
        }

        //构建数据
        $data                   = array(
            'type'              => self::$type,
            'resource_id'       => self::$resource_id,
            'user_id'           => self::$user_id,
            'comment'           => self::$comment,
            'state'             => 0
        );

        $commentM               = M('forum_comment');

        $result                 = $commentM->add($data);

        if(!$result) {
            return false;
            die();
        }

        return true;
        die();
    }

    protected static function addReply() {

        if(!self::$user_id || !is_numeric(self::$parent_id) || !self::$comment_id || !self::$reply) {
            return false;
            die();
        }

        //构建数据
        $data               = array(
            'parent_id'     => self::$parent_id,
            'comment_id'    => self::$comment_id,
            'user_id'       => self::$user_id,
            'reply'         => self::$reply,
            'push_time'     => time(),
            'state'         => 0,
        );

        $replyM             = M('forum_reply');

        $result             = $replyM->add($data);

        if(!$result) {
            return false;
            die();
        }

        return true;
        die();
    }
}