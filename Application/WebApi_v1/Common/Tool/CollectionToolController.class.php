<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/2
 * Time: 20:13
 */
namespace WebApi_v1\Common\Tool;


class CollectionToolController
{
    /**
     * 资源收藏事件(取消收藏)
     * $user_id 用户id
     * $resource_id 资源id
     * $type 類型 1-視頻，2-圖集，3-小説
     * $like_id 收藏id
     */
    protected static $type;
    protected static $user_id;
    protected static $resource_id;

    //收藏
    public static function collect($user_id,$type,$resource_id) {

        self::$user_id          = $user_id;
        self::$resource_id      = $resource_id;
        self::$type             = $type;

        return self::add();
    }

    //取消收藏
    public static function cancel($user_id,$type,$resource_id) {

        self::$user_id          = $user_id;
        self::$type             = $type;
        self::$resource_id      = $resource_id;

        return self::delete();
    }

    /*
     * 添加收藏记录及资源表收藏字段自增 +1
     */
    protected static function add() {

        if (!self::$user_id || !self::$resource_id || !self::$type) {
            return false;
            die();
        }

        //组合数据
        $data                   = array(
            'user_id'           => self::$user_id,
            'type'              => self::$type,
            'resource_id'       => self::$resource_id,
            'time'              => time()
        );

        $likeM                  = M('notice_like');

        //添加
        $result                 = $likeM->add($data);

        if(!$result) {
            return false;
            die();
        }

        switch (self::$type) {
            case 1:
                $movieM         = M('resource_movie');
                //该资源 资源表收藏字段自增 1
                return $movieM->where(array('movie_id' => self::$resource_id))->setInc('like_count');
                break;
            case 2:
                $imageM         = M('resource_image');
                //该资源 资源表收藏字段自增 1
                return $imageM->where(array('image_id' => self::$resource_id))->setInc('like_count');
                break;
            case 3:
                $fictionM       = M('resource_fiction');
                //该资源 资源表收藏字段自增 1
                return $fictionM->where(array('fiction_id' => self::$resource_id))->setInc('like_count');
                break;
        }
    }

    /*
     * 取消收藏 删除收藏记录及资源表收藏字段自减 -1
     */
    protected static function delete() {

        if(!self::$resource_id || !self::$type || !self::$user_id) {
            return false;
            die();
        }

        $likeM          = M('notice_like');

        //删除
        $result         = $likeM->where(array('type' => self::$type,'resource_id'=>self::$resource_id,'user_id'=>self::$user_id))->delete();

        if(!$result) {
            return false;
            die();
        }

        switch (self::$type) {
            case 1:
                $movieM         = M('resource_movie');
                //该资源 资源表收藏字段自增 1
                return $movieM->where(array('movie_id' => self::$resource_id))->setDec('like_count');
                break;
            case 2:
                $imageM         = M('resource_image');
                //该资源 资源表收藏字段自增 1
                return $imageM->where(array('image_id' => self::$resource_id))->setDec('like_count');
                break;
            case 3:
                $fictionM       = M('resource_fiction');
                //该资源 资源表收藏字段自增 1
                return $fictionM->where(array('fiction_id' => self::$resource_id))->setDec('like_count');
                break;
        }
    }
}