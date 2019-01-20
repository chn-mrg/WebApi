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
     * 發佈動態
     * $type 類型：1-視頻，2-圖片，3-文字
     * $object 内容
     */
    public function publishDynamic() {

        $userInfo               = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }
        //判斷用戶是否可以發佈動態
        $level                  = self::level($userInfo['experience']);
        if($level['level'] < 10) {
            self::returnAjax(100013);
        }
        $type                   = I('type'); //類型：1-視頻，2-圖片，3-文字
        if(!$type) {
            self::returnAjax(100005);
        }

        //視頻動態
        if($type == 1) {
            $text               = I('text'); //視頻文字
            $video_img          = I('video_img'); //視頻封面圖地址
            $video_url          = I('video_url'); //視頻地址
            $long               = I('long'); //視頻時長
            $urlRule            = C('urlRule');
            if(!$video_img || !$video_url || !$long || strstr($urlRule['ResourceUrl'], $video_img) == false || strstr($urlRule['ResourceUrl'], $video_url) == false) {
                self::returnAjax(100005);
            }
            //對文字長度判斷
            if($text) {
                if(abslength($text) > 250) {
                    self::returnAjax(100014);
                }
            }

            $object             = array(
                'text'          => $text,
                'video_img'     => $video_img,
                'video_url'     => $video_url,
                'long'          => $long
            );
        }

        //圖片動態
        if($type == 2) {
            $text               = I('text'); //圖片文字
            $img_url            = I('img_url'); //圖片地址(數組)
            if(!$img_url) {
                self::returnAjax(100005);
            }
            $long               = count($img_url); //圖片數量
            $urlRule            = C('urlRule');
            foreach ($img_url as $k => $v) {
                if(strstr($urlRule['ResourceUrl'], $v) == false) {
                    self::returnAjax(100005);
                }
            }
            //對文字長度判斷
            if($text) {
                if(abslength($text) > 250) {
                    self::returnAjax(100014);
                }
            }

            $object             = array(
                'text'          => $text,
                'img_url'       => $img_url,
                'long'          => $long
            );
        }

        //文字動態
        if($type == 3) {
            $text               = I('text'); //文字
            if(!$text) {
                self::returnAjax(100005);
            }

            //對文字長度判斷
            if(abslength($text) > 250) {
                self::returnAjax(100014);
            }

            $object             = array(
                'text'          => $text
            );
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

        $result             = M('forum_dynamic')->add($data);
        if($result) {
            self::returnAjax(200);
        }else{
            self::returnAjax(301);
        }
    }
}