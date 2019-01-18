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
     * type 0-正常私信，2-用戶評論，3-用戶回復，4-用戶點贊 5-系统消息
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
            $noticeList     = $noticeM->field('')->where(array())->order('time')->page($page,$num)->select();
        }
    }
}