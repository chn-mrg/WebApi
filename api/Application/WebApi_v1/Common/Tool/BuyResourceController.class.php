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
    protected static $pey_type; //购买方式 0-资源券购买 1-G点购买
    protected static $money; //花費


    public static function mode($user_id,$type,$resource_id,$pey_type,$money)
    {
        self::$user_id          = $user_id;
        self::$type             = $type;
        self::$resource_id      = $resource_id;
        self::$pey_type         = $pey_type;
        self::$money            = $money;

        return self::buyResource();
    }

    //购买
    protected static function buyResource()
    {
        if(!self::$user_id || !self::$type || !self::$resource_id || !is_numeric(self::$pey_type) || !self::$money) {
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

        if($result) {
            if(self::$pey_type == 0) {
                //减少用户资源券
                M('user_list')->where(array('user_id'=>self::$user_id))->setDec('watch',self::$money);
                //添加资源券消费记录
                $watchData      = array(
                    'user_id'   => self::$user_id,
                    'watch'     => self::$money,
                    'type'      => 0, //支出
                    'time'      => time(),
                    'memo'      => '购买资源支出'
                );
                M('user_watch')->add($watchData); //添加记录
            }
            if (self::$pey_type == 1) {
                //减少用户G点
                M('user_list')->where(array('user_id'=>self::$user_id))->setDec('money',self::$money);
                //添加G点消费记录
                $moneyData      = array(
                    'user_id'   => self::$user_id,
                    'money'     => self::$money,
                    'type'      => 0, //支出
                    'time'      => time(),
                    'memo'      => '购买资源支出'
                );
                M('user_money')->add($moneyData); //添加记录
            }

            return true;
        }else{
            return false;
            die();
        }

    }


    //失效时间（24小时后）
    private static function invalid() {
        return time() + 24 * 60 * 60 * 1000;
    }

}