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
        $FilePath = "Img/" . date('Ymd') . "/" . date('YmdHis') . mt_rand(10000000, 99999999);
        $result = self::UploadImgFunction($_FILES['file'],$FilePath);
        if($result['result']){
            self::returnAjax(200,array('key'=>$result['url'],'url'=>self::ResourceAwsS3Url($result['url'])));
        }
        self::returnAjax(301,array('reason'=>$result['reason']));
    }

    public function uploadImgBatch(){
        foreach ($_FILES['file']['tmp_name'] as $k=>$v){
            $FilePath = "Img/" . date('Ymd') . "/" . date('YmdHis') . mt_rand(10000000, 99999999);
            $result = self::UploadImgFunction(array('tmp_name'=>$v),$FilePath);
            if($result['result']){
                $data[] = array('key'=>$result['url'],'url'=>self::ResourceAwsS3Url($result['url']));
            }
        }
        if(is_array($data)){
            self::returnAjax(200,$data);
        }
        self::returnAjax(301);
    }

    public function uploadMp4(){
        $FilePath = "TemMp4/" . date('Ymd') . "/" . date('YmdHis') . mt_rand(10000000, 99999999);
        $result = self::UpLoadMp4Function($_FILES['file'],$FilePath);
        if($result['result']){
            self::returnAjax(200,array('key'=>$result['url'],'url'=>self::ResourceAwsS3Url($result['url'])));
        }
        self::returnAjax(301,array('reason'=>$result['reason']));
    }

    public function uploadMp3(){
        $FilePath = "Mp3/" . date('Ymd') . "/" . date('YmdHis') . mt_rand(10000000, 99999999);
        $result = self::UpLoadMp3Function($_FILES['file'],$FilePath);
        if($result['result']){
            self::returnAjax(200,array('key'=>$result['url'],'url'=>self::ResourceAwsS3Url($result['url'])));
        }
        self::returnAjax(301,array('reason'=>$result['reason']));
    }
}