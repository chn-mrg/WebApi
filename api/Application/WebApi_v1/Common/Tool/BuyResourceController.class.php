<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/17
 * Time: 下午 03:51
 */

namespace WebApi_v1\Common\Tool;


class BuyResourceController
{
    /**
     * 购买资源事件
     */

    protected static $user_id;
    protected static $type; //1-视频，2-图片，3-小说
    protected static $resource_id; //资源id
    protected static $pey_type; //购买方式 1-资源券购买 2-G点购买


    public static function mode($user_id,$type,$resource_id,$pey_type)
    {
        self::$user_id          = $user_id;
        self::$type             = $type;
        self::$resource_id      = $resource_id;
        self::$pey_type         = $pey_type;

    }

    //购买
    protected static function buyResource()
    {
        if(!self::$user_id || !self::$type || !self::$resource_id || !self::$pey_type) {
            return false;
            die();
        }

        //公共构建数据
        $data               = array(
            'user_id'       => self::$user_id,
            'type'          => self::$type,
            'resource_id'   => self::$resource_id,
            'pey_type'      => self::$pey_type,
            'pey_time'      => time()
        );

        if(self::$pey_type == 1) {
            $data['out_time'] = 0;
        }else{
            //失效时间
            $time               = self::invalid();
            $data['out_time']   = $time;
        }
        $resourceM          = M('user_resource');
        $result             = $resourceM->add($data);

        if(!$result) {
            return false;
            die();
        }

        return true;
    }


    //失效时间（24小时后）
    private static function invalid() {
        return time() + 24 * 60 * 60 * 1000;
    }

}