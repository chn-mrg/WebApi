<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/21
 * Time: 下午 07:00
 */

namespace WebApi_v1\Controller;


class HistoryController extends UserBaseController
{
    /*
     * 添加歷史記錄
     * cookie 存儲
     * type 類型 1-視頻 2-圖片 3-小説 4-動態
     * resource_id 資源id
     * time 瀏覽時間
     */
    public function setCookieHistory() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }

        $type               = I('type');
        $resource_id        = I('resource_id');
        if(!$type || !$resource_id) {
            self::returnAjax(100005);
        }


    }
}