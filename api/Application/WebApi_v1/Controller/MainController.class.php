<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/11/26
 * Time: 12:58
 */

namespace WebApi_v1\Controller;

use Think\Controller;

class MainController extends Controller
{
    private $SysConf;
    public function __construct(){
        $SysConfM = M('sys_conf');
        $this->SysConf = $SysConfM->cache((C('cacheName'))['SysConfCache'])->select();
        parent::__construct();
    }

    /*
     * 重寫ajaxReturn
     */
    protected function returnAjax($code,$Data = false){
        $ReturnArray = array('code'=>$code,'msg'=>(C('CodeMsg'))[$code]);
        if($Data){$ReturnArray['data']=$this->DataToString($Data);}
        $this->ajaxReturn($ReturnArray,'JSON');
    }

    /*
     * 將array内的值全部string
     */
    private function DataToString($Data){
        foreach ($Data as $k=>$v){
            if(is_array($v)){
                $Data[$k] = $this->DataToString($v);
            }else{
                $Data[$k] = $v."";
            }
        }
        return $Data;
    }

    /*
     * 轉換資源url
     */
    protected function ResourceUrl($url){
        $ResourceUrlConf = $this->GetSysConf('ResourceUrl');
        return str_replace((C('urlRule'))['ResourceUrl'],$ResourceUrlConf['value'],$url);
    }

    /*
     * 轉換資源url
     */
    protected function ResourceAwskey($url){
        return str_replace((C('urlRule'))['ResourceUrl']."/","",$url);
    }

    /*
     * 轉換資源url
     */
    protected function ResourceAwsS3Url($url){
        $ResourceUrlConf = $this->GetSysConf('AwsS3Url');
        return str_replace((C('urlRule'))['ResourceUrl'],$ResourceUrlConf['value'],$url); //替换
    }

    /*
     * 讀取指定SysConf
     */
    protected function GetSysConf($key){
        foreach ($this->SysConf as $k => $v){
            if($v['key']==$key){
                return $v;
            }
        }
    }

    /*
     * 獲取配置列表
     */
    protected function getSysConfList(){
        return $this->SysConf;
    }

    /*
     * 判斷URL是否是img
     */
    protected function isImgUrl($imgUrl)
    {
        //获取文件mime类型
        $ch = curl_init($imgUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_exec($ch);
        $return_content = ob_get_contents ();
        ob_end_clean ();
        $mime=curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $mimeArray=explode('/',$mime);
        //print_r($mimeArray);
        return $mimeArray[0] == 'image' ? true : false;
    }


    /*
     * 刪除指定Cache
     */
    protected function deleteCache($CacheName){
        return S($CacheName,null);
    }


    /*
     * json轉m3u8
     */
    protected function JsonToM3u8($jsonUrl){
        $request = self::GetRequest($jsonUrl);
        $m3u8JsonArray = (array)json_decode($request);
        if($request && is_array($m3u8JsonArray)) {
            $m3u8 = "#EXTM3U" .
                PHP_EOL . "#EXT-X-VERSION:3" .
                PHP_EOL . "#EXT-X-MEDIA-SEQUENCE:0" .
                PHP_EOL . "#EXT-X-ALLOW-CACHE:YES" .
                PHP_EOL . "#EXT-X-TARGETDURATION:" . $m3u8JsonArray['targetduration'];
            foreach ($m3u8JsonArray['ts_list'] as $k => $v) {
                $v = (array)$v;
                $m3u8 = $m3u8 . PHP_EOL . "#EXTINF:" . $v['ts_time'] . "," .
                        PHP_EOL . self::ResourceAwsS3Url((C('urlRule'))['ResourceUrl'] . "/" . $v['ts_path']);

            }
            $m3u8 = $m3u8 . PHP_EOL . "#EXT-X-ENDLIST";
            return $m3u8;
        }
        return false;
    }

    /*
     * array轉m3u8
     */
    public function ArrayToM3u8($array){
        $m3u8 = "#EXTM3U" .
            PHP_EOL . "#EXT-X-VERSION:3" .
            PHP_EOL . "#EXT-X-MEDIA-SEQUENCE:0" .
            PHP_EOL . "#EXT-X-ALLOW-CACHE:YES" .
            PHP_EOL . "#EXT-X-TARGETDURATION:" . $array['targetduration'];
        foreach ($array['ts_list'] as $k => $v) {
            $v = (array)$v;
            $m3u8 = $m3u8 . PHP_EOL . "#EXTINF:" . $v['ts_time'] . "," .
                PHP_EOL . self::ResourceAwsS3Url((C('urlRule'))['ResourceUrl'] . "/" . $v['ts_path']);

        }
        $m3u8 = $m3u8 . PHP_EOL . "#EXT-X-ENDLIST";
        return $m3u8;
    }

    /*
     * post請求
     */
    protected function PostRequest($url,$post_data = array()){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $data = curl_exec($curl);
        if($data === FALSE)
        {
            echo "<br/>","cUrl Error:".curl_error($curl);
        }
        curl_close($curl);
        return $data;
    }

    /*
     * Get請求
     */
    protected function GetRequest($url){
        $curl=curl_init($url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_BINARYTRANSFER,true);
        $data=curl_exec($curl);
        if($data === FALSE)
        {
            echo "<br/>","cUrl Error:".curl_error($curl);
        }
        curl_close($curl);
        return $data;
    }

}