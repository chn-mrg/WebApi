<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/11/27
 * Time: 23:04
 */

namespace WebApi_v1\Controller;


class UploadFileController extends UserBaseController
{
    public function uploadImg(){
        $userInfo = self::getUserInfo();
        $level = $userInfo?self::level($userInfo['experience']):0;
        if($level['level']>=10) {
            $FilePath = "Img/User_".$userInfo['user_id']."/" . date('Ymd') . "/" . date('YmdHis') . mt_rand(10000000, 99999999);
            $result = self::UploadImgFunction($_FILES['file'], $FilePath);
            if ($result['result']) {
                self::returnAjax(200, array('key' => $result['url'], 'url' => self::ResourceAwsS3Url($result['url'])));
            }
            self::returnAjax(301, array('reason' => $result['reason']));
        }
        self::returnAjax(301, array('reason' => '用戶等級不足10級'));
    }

    public function uploadImgBatch(){
        $userInfo = self::getUserInfo();
        $level = $userInfo?self::level($userInfo['experience']):0;
        if($level['level']>=10) {
            foreach ($_FILES['file']['tmp_name'] as $k => $v) {
                $FilePath = "Img/" . date('Ymd') . "/" . date('YmdHis') . mt_rand(10000000, 99999999);
                $result = self::UploadImgFunction(array('tmp_name' => $v), $FilePath);
                if ($result['result']) {
                    $data[] = array('key' => $result['url'], 'url' => self::ResourceAwsS3Url($result['url']));
                }
            }
            if (is_array($data)) {
                self::returnAjax(200, $data);
            }
            self::returnAjax(301);
        }
        self::returnAjax(301, array('reason' => '用戶等級不足10級'));
    }

    public function uploadMp4(){
        $userInfo = self::getUserInfo();
        $level = $userInfo?self::level($userInfo['experience']):0;
        if($level['level']>=10) {
            $FilePath = "TemMp4/User_".$userInfo['user_id']."/"  . date('Ymd') . "/" . date('YmdHis') . mt_rand(10000000, 99999999);
            $result = self::UpLoadMp4Function($_FILES['file'], $FilePath);
            if ($result['result']) {
                self::returnAjax(200, array('key' => $result['url'], 'url' => self::ResourceAwsS3Url($result['url'])));
            }
            self::returnAjax(301, array('reason' => $result['reason']));
        }
        self::returnAjax(301, array('reason' => '用戶等級不足10級'));
    }

//    public function uploadMp3(){
//        $FilePath = "Mp3/" . date('Ymd') . "/" . date('YmdHis') . mt_rand(10000000, 99999999);
//        $result = self::UpLoadMp3Function($_FILES['file'],$FilePath);
//        if($result['result']){
//            self::returnAjax(200,array('key'=>$result['url'],'url'=>self::ResourceAwsS3Url($result['url'])));
//        }
//        self::returnAjax(301,array('reason'=>$result['reason']));
//    }
}