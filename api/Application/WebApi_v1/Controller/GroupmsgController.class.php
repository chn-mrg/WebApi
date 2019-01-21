<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/21
 * Time: 18:09
 */

namespace WebApi_v1\Controller;


use GatewayClient\Gateway;

class GroupmsgController extends UserBaseController
{

    public function GetWebSocketUrl(){
        $conf = self::GetSysConf("WebsocketUrl");
        if($conf){
            self::returnAjax(200,array('url'=>$conf['value']));
        }
        self::returnAjax(404);
    }

    public function BindClientId(){
        $userInfo = self::getUserInfo();
        $client_id = I('client_id');
        $groupName = I('group');
        if($userInfo && $client_id &&($groupName=="group" || $groupName=="live")){
            $GatewayClient = new Gateway();
            $conf = self::GetSysConf("RegisterAddress");
            $GatewayClient::$registerAddress = $conf['value'];

            $uId = $userInfo['user_id'];
            $GatewayClient::bindUid($client_id, $uId);
            $GatewayClient::joinGroup($client_id, $groupName);
            self:: returnAjax(200);
        }else{
           self:: returnAjax(100000);
        }
    }

    public function sendGroupMsg(){
        $userInfo = self::getUserInfo();
        $text = I('text');
        $groupName = I('group');
        if($userInfo && $text &&($groupName=="group" || $groupName=="live")){
            $GatewayClient = new Gateway();
            $conf = self::GetSysConf("RegisterAddress");
            $GatewayClient::$registerAddress = $conf['value'];
            $levelInfo = self::level($userInfo['experience']);
            $textJson = array(
                'user_id'=>$userInfo['user_id']."",
                'nickname'=>$userInfo['nickname']."",
                'head_portrait'=>self::ResourceUrl($userInfo['head_portrait'])."",
                'level'=>$levelInfo['level']."",
                'level_icon'=>$levelInfo['icon']."",
                'text' =>$text."",
                'time'=>time()."",
            );
            $GatewayClient::sendToGroup($groupName,json_encode($textJson));
            self:: returnAjax(200,'發送成功');
        }else{
            self:: returnAjax(301,'發送失敗');
        }
    }

}