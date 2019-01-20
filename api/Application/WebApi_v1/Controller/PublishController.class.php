<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/20
 * Time: 下午 05:56
 */

namespace WebApi_v1\Controller;


class PublishController extends UserBaseController
{
    /*
     * 發佈動態 (純文字)
     * $type 發佈類型 1-視頻，2-圖片，3-文字
     * $object 内容
     */
    public function publishDynamic() {

        $userInfo               = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }

        //判斷用戶是否有發佈動態權限（等級 >= 10）
        $level                  = self::level($userInfo['experience']);

        if($level['level'] >= 10) {
            $object             = I('text');
            if(!$object) {
                self::returnAjax(100005);
            }
            //判斷字數 >= 250
            if(self::abslength($object) >= 250) {
                self::returnAjax(100014);
            }

            //構建數據
            $data               = array(
                'user_id'       => $userInfo['user_id'],
                'type'          => 3, //類型：1-視頻，2-圖片，3-文字
                'time'          => time(),
                'like_count'    => 0,
                'object'        => json_encode($object),
                'comment_count' => 0,
                'is_forward'    => 0,
                'forward_id'    => 0,
                'state'         => 0
            );


        }else{
            self::returnAjax(100013); //等級未達到10級
        }

    }

}