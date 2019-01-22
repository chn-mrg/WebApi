<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/11/26
 * Time: 13:14
 */

namespace WebApi_v1\Controller;
require_once "./ThinkPHP/Library/Aws/aws-autoloader.php";

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class UserBaseController extends MainController
{
    /*
     * 當前用户信息
     */
    private $userInfo;

    /*
     * 驗證用户登錄狀態
     */
    public function __construct()
    {
        parent::__construct();

        if($_SESSION['user']){
            $UserM              = M('user_list');

            $userId             = $_SESSION['user']['user_id']; //用户id
            $userToken          = $_SESSION['user']['user_token']; //用户token

            $this->userInfo     = $UserM ->where(array('user_id' => $userId,'user_token' => $userToken))->find();

            if(!$this->userInfo || $this->userInfo['state'] == 2){
                $this->userInfo = null;
            }
        }else{
            $this->userInfo     = null;
        }
    }

    /*
     * 獲取當前用户信息
     */
    protected function getUserInfo(){
        return $this->userInfo;
    }

    /*
     * 上傳圖片 $file = $_FILES[]
     */
    protected function UploadImgFunction($file,$AwsKey)
    {
        if($file) {
            $fileType = $this->isImgFunction($file['tmp_name']);
            if ($fileType) {
                $FilePath = $AwsKey. ".".$fileType;
                $S3client = new S3Client(C('S3Client'));
                try {
                    $data = array(
                        'ACL' => 'public-read',
                        'Bucket' => 'sex-s3', // REQUIRED
                        'Body'=>fopen($file['tmp_name'], 'r'),
                        'Key' => $FilePath, // REQUIRED
                        'StorageClass' => 'STANDARD',
                        'ContentType'=>'image/'.$fileType,
                    );
                    $Result = $S3client->putObject($data);
                    fclose($data['Body']);
                    if($Result->get('ObjectURL')){
                        return array('result' => true, 'url' => (C('urlRule'))['ResourceUrl'].'/' . $FilePath);
                    }
                    return array('result' => false, 'reason' => 'upload failure');
                } catch (S3Exception $e) {
                    return array('result' => false, 'reason' => 'upload failure'.$e->getMessage());
                }
            }
            return array('result' => false, 'reason' => 'no picture');
        }
        return array('result' => false, 'reason' => 'no picture');
    }

    /*
     * 上傳mp4 $file = $_FILES[]
     */
    protected function UpLoadMp4Function($file,$AwsKey){
        if($file) {
            $fileType = $this->isMp4Function($file['tmp_name']);
            if ($fileType) {
                $FilePath =$AwsKey . ".".$fileType;
                $S3client = new S3Client(C('S3Client'));
                try {
                    $data = array(
                        'ACL' => 'public-read',
                        'Bucket' => 'sex-s3', // REQUIRED
                        'Body'=>fopen($file['tmp_name'], 'r'),
                        'Key' => $FilePath, // REQUIRED
                        'StorageClass' => 'STANDARD',
                        'ContentType'=>'video/mp4',
                    );
                    $Result = $S3client->putObject($data);
                    fclose($data['Body']);
                    if($Result->get('ObjectURL')){
                        return array('result' => true, 'url' => (C('urlRule'))['ResourceUrl'].'/' . $FilePath);
                    }
                    return array('result' => false, 'reason' => 'upload failure');
                } catch (S3Exception $e) {
                    return array('result' => false, 'reason' => 'upload failure'.$e->getMessage());
                }
            }
            return array('result' => false, 'reason' => 'no mp4');
        }
        return array('result' => false, 'reason' => 'no mp4');
    }

    /*
     * 上傳mp3 $file = $_FILES[]
     */
    protected function UpLoadMp3Function($file,$AwsKey){
        if($file) {
            $fileType = $this->isMp3Function($file['tmp_name']);
            if ($fileType) {
                $FilePath =$AwsKey . ".".$fileType;
                $S3client = new S3Client(C('S3Client'));
                try {
                    $data = array(
                        'ACL' => 'public-read',
                        'Bucket' => 'sex-s3', // REQUIRED
                        'Body'=>fopen($file['tmp_name'], 'r'),
                        'Key' => $FilePath, // REQUIRED
                        'StorageClass' => 'STANDARD',
                        'ContentType'=>'audio/mp3',
                    );
                    $Result = $S3client->putObject($data);
                    fclose($data['Body']);
                    if($Result->get('ObjectURL')){
                        return array('result' => true, 'url' => (C('urlRule'))['ResourceUrl'].'/' . $FilePath);
                    }
                    return array('result' => false, 'reason' => 'upload failure');
                } catch (S3Exception $e) {
                    return array('result' => false, 'reason' => 'upload failure'.$e->getMessage());
                }
            }
            return array('result' => false, 'reason' => 'no mp4');
        }
        return array('result' => false, 'reason' => 'no mp4');
    }


    /*
     * 判斷是否為img
     * return imgtype
     */
    private function isImgFunction($fileName)
    {
        $file     = fopen($fileName, "rb");
        $bin      = fread($file, 2);  // 只读2字节
        fclose($file);
        $strInfo  = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);

        switch ($typeCode){
            case 255216:
                return "jpg";
                break;
            case 7173:
                return "gif";
                break;
            case 13780:
                return "png";
                break;
            default:
                return false;
        }
    }


    /*
     * 判斷是否為mp4
     * return mp4
     */
    private function isMp4Function($fileName){
        vendor('getid3.getid3');
        $getId3 = new \getID3();
        $info = $getId3->analyze($fileName);
        if($info['fileformat'] && $info['fileformat']=='mp4'){
            return "mp4";
        }
        return false;
    }

    /*
     * 判斷是否為mp3
     * return mp4
     */
    private function isMp3Function($fileName){
        vendor('getid3.getid3');
        $getId3 = new \getID3();
        $info = $getId3->analyze($fileName);
        if($info['fileformat'] && $info['fileformat']=='mp3'){
            return "mp3";
        }
        return false;
    }

    /*
     * 下載資源
     */
    protected function Download($url,$path){

        try {
            $fp_output = fopen($path, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp_output);
            curl_exec($ch);
            curl_close($ch);
            return true;
        }catch (Exception $e){
            return false;
        }
    }

    /*
     * 創建文件夾並賦予777權限
     */
    protected function create_folders($dir){
        return is_dir($dir) or ($this->create_folders(dirname($dir)) and mkdir($dir, 0777));
    }

    /*
     * 通過id獲取標簽名稱
     */
    protected function getLabelArray($labelIds){
        $sysLabelListM = M('sys_label_list');
        $LabelList = $sysLabelListM->cache((C('cacheName'))['SysLabelCache'])->select();
        $LabelArray = array();
        foreach ($labelIds as $k=>$v){
            foreach ($LabelList as $k1=>$v1){
                if($v==$v1['label_id']){
                    $LabelArray[]=array(
                        'label_id'=>$v1['label_id'],
                        'label_name'=>$v1['label_name']
                    );
                }
            }
        }
        return $LabelArray;
    }

    /*
     * 獲取等級配置
     */
    protected function SysLevelList(){
        $SysLevel = M('sys_level');
        return $SysLevel->cache((C('cacheName'))['SysLevelCache'])->order('experience ASC')->select();
    }

    /*
     * 判断用户是否有此资源观看权限
     * 参数
     * type (1、视频 2、图片 3、小说)
     * user_id 用户id
     * resource_id 资源id
     */
    protected function isResourceAuth($type, $user_id, $resource_id) {

        if(!$type || !$user_id || !$resource_id) {
            return false;
            die();
        }

        $where['type']              = $type;
        $where['user_id']           = $user_id;
        $where['resource_id']       = $resource_id;

        $userResourceM              = M('user_resource');

        $userResource               = $userResourceM->where($where)->find();

        if(!$userResource) {
            return false;
            die();
        }

        //已购买此资源，0 永久
        if($userResource['out_time'] == 0) {
            return true;
            die();
        }

        //有失效时间，判断是否已失效
        if($userResource['out_time'] > time()) {
            return true;
            die();
        }else{
            return false;
            die();
        }
    }

    /*
     * 查看评论
     * 参数
     * type 类型 0-社區動態，1-視頻，2-圖片，3-小説
     * resource_id 资源id
     * page 当前页 非必填 默认 第1页
     */
    protected function commentInfo($type, $resource_id, $page) {

        if(!$resource_id || !$page || !is_numeric($type)) {
            return false;
            die();
        }

        $num                    = 5;

        $where['a.type']        = $type;
        $where['a.resource_id'] = $resource_id;
        $where['a.state']       = 1; //评论审核状态 0-未審核，1-審核通過，2-審核失敗
        $where['b.state']       = 1; //评论用户状态 0-不可發言，1-正常，2-已封號

        $commentM               = M('forum_comment');

        $commentInfo            = $commentM
                                ->alias('a')
                                ->field('a.comment_id,a.user_id,a.comment,a.push_time, b.user_id,b.nickname,b.head_portrait')
                                ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                ->where($where)
                                ->order('push_time DESC')
                                ->page($page,$num)
                                ->select();
        $commentInfoCount       = $commentM
                                ->alias('a')
                                ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                ->where($where)
                                ->count();

        if(!$commentInfo) {
            return false;
            die();
        }

        foreach ($commentInfo as $k => $v) {
            //用户头像路径
            $commentInfo[$k]['head_portrait']       = self::ResourceUrl($v['head_portrait']);
            //评论时间转换为 多少秒前、多少分钟前...几年前
            $commentInfo[$k]['push_time']           = self::formatDate($v['push_time']);

            //该评论显示3条回复 及 总回复数
            $replyM             = M('forum_reply');

            //前三条一级回复
            $commentInfo[$k]['replyThree']          = $replyM
                                                    ->alias('a')
                                                    ->field('a.reply_id,a.reply,a.user_id,a.parent_id,a.comment_id, b.user_id,b.nickname')
                                                    ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                                    ->where(array('parent_id' => 0, 'a.comment_id' => $v['comment_id'],'a.state' => 1,'b.state' => 1))
                                                    ->limit(3)
                                                    ->order('a.push_time')
                                                    ->select();
            //该评论下的所有回复(多级)
            $commentInfo[$k]['replyCount']          = $replyM
                                                    ->alias('a')
                                                    ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                                    ->where(array('a.comment_id' => $v['comment_id'],'a.state' => 1,'b.state' => 1))
                                                    ->count();
        }

        return array('pages'=>array('count'=>$commentInfoCount,'num'=>$num),'list'=>$commentInfo);
    }

    /*
     * 查看全部回复
     * 参数
     * comment_id 评论id
     * page 当前页 非必填 默认 第1页
     */
    protected function getAllReply($comment_id,$page) {

        if(!$comment_id) {
            return false;
            die();
        }

        $commentM                   = M('forum_comment');
        $replyM                     = M('forum_reply');

        //该条评论信息
        $commentInfo                = $commentM
                                    ->alias('a')
                                    ->field('a.comment_id,a.user_id,a.comment,a.push_time, b.nickname,b.head_portrait,b.experience')
                                    ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                    ->where(array('a.comment_id'=>$comment_id))
                                    ->find();
        if(!$commentInfo) {
            return false;
            die();
        }

        //该条评论用户头像
        $commentInfo['head_portrait'] = self::ResourceUrl($commentInfo['head_portrait']);
        //评论时间转换
        $commentInfo['push_time']   = self::formatDate($commentInfo['push_time']);
        //该用户等级
        $level                      = self::level($commentInfo['experience']);
        if($level){
            $commentInfo['level']   = $level['level'];
            $commentInfo['icon']    = $level['icon'];
        }

        //该条评论下的所有审核通过回复
        $replyList                  = $replyM
                                    ->alias('a')
                                    ->field('a.reply_id,a.parent_id,a.user_id,a.push_time,a.reply,a.parent_id,a.comment_id, b.nickname,b.head_portrait')
                                    ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                    ->where(array('a.comment_id'=>$comment_id,'a.state'=>1))
                                    ->order('push_time')
                                    ->page($page,8)
                                    ->select();
        $replyCount                 = $replyM
                                    ->alias('a')
                                    ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                    ->where(array('a.comment_id'=>$comment_id,'a.state'=>1))
                                    ->count();

        if($replyList) {
            foreach ($replyList as $k => $v) {
                //回复者头像
                $replyList[$k]['head_portrait']     = self::ResourceUrl($v['head_portrait']);

                //回复回复时 被回复用户信息
                if($v['parent_id'] != 0) {
                    $replyList[$k]['toUser']        = $replyM
                                                    ->alias('a')
                                                    ->field('a.reply_id,a.user_id,b.nickname')
                                                    ->join('LEFT JOIN sex_user_list b ON b.user_id = a.user_id')
                                                    ->where(array('a.reply_id'=>$v['parent_id'],'a.state'=>1))
                                                    ->find();
                }

                //回复时间转换
                $replyList[$k]['push_time']         = self::formatDate($v['push_time']);
            }
        }else{
            $replyList              = array();
            $replyCount             = 0;
        }

        return array('comment'=>$commentInfo,'replyList'=>array('pages'=>array('count'=>$replyCount,'num'=>6),'list'=>$replyList));
    }

    /*
     * 判断A用户是否已关注B用户
     * 参数
     * user_id 關注者id
     * touser_id 被關注者id
     */
    protected function userTouser($user_id, $touser_id) {

        if(!$user_id || !$touser_id) {
            return false;
            die();
        }

        $fansM          = M('forum_fans');

        $result         = $fansM->where(array('user_id' => $user_id,'touser_id' => $touser_id))->find();

        if(!$result) {
            return false;
            die();
        }

        return true;
        die();
    }

    /*
     * 判断资源是否已被用户收藏
     * user_id 用户id
     * resource_id 资源id
     * type 類型 1-視頻，2-圖集，3-小説
     */
    protected function isCollection($user_id,$resource_id,$type) {

        if(!$user_id || !$resource_id || !$type) {
            return false;
            die();
        }

        $collectionM        = M('notice_like');

        $result             = $collectionM->where(array('user_id'=>$user_id,'resource_id'=>$resource_id,'type'=>$type))->find();

        if(!$result) {
            return false;
            die();
        }

        return true;
        die();
    }

    /*
     * 判断用户是否已点赞该动态
     * 参数
     * user_id 用户id
     * dynamic_id 动态id
     */
    protected function isLike($user_id,$dynamic_id) {

        if (!$user_id || !$dynamic_id) {
            return false;
            die();
        }

        $likeM          = M('forum_like');

        $result         = $likeM->where(array('user_id'=>$user_id,'dynamic_id'=>$dynamic_id))->find();

        if(!$result) {
            return false;
            die();
        }

        return true;
        die();
    }

    /*
     * 获取用户等级及等级标志
     * experience 用户经验
     */
    protected function level($experience) {

        if(!is_numeric($experience)) {
            return false;
            die();
        }

        $data                           = array();
        $levelList                      = self::SysLevelList(); //获取等级配置
        if($levelList) {
           foreach ($levelList as $k => $v) {
               if($experience >= $v['experience']) {
                   $data['level']       = $v['level']; //等级名称
                   $data['icon']        = self::ResourceUrl($v['icon']);
               }
           }
        }else{
            return false;
            die();
        }

        return $data;
    }

    /*
     * 将一个中文转换成一个字符
     */
    protected function abslength($str)
    {
        if(empty($str)){
            return 0;
        }
        if(function_exists('mb_strlen')){
            return mb_strlen($str,'utf-8');
        }
        else {
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }

    /*
     * 關鍵字過濾
     * $str 字符串
     */
    protected function keyFilter($str) {

        $keyword            = C('Keyword');
        // 字符串转数组
        $strArr             = explode('、',$str);

        //去掉空格
        foreach ($strArr as $k => $v) {

        }

    }

    /*
     * 判斷用戶是否已購買此資源
     * type 資源類型
     * resource_id 資源id
     * user_id 用戶id
     */
    protected function watchAuth($user_id, $type, $resource_id) {

        if(!$user_id || !$type || !$resource_id) {
            return false;
            die();
        }

        //查詢此用戶是否已購買此資源
        $resourceM              = M('user_resource');
        $resource               = $resourceM->where(array('user_id'=>$user_id,'type'=>$type,'resource_id'=>$resource_id))->find();
        if($resource) {
            //判斷類型   若爲資源券購買  需判斷是否過期
            if($resource['pey_type'] == 0) {
                if($resource['out_time'] < time()) { //已過期
                    return false;
                    die();
                }else{
                    return true;
                    die();
                }
            }else{
                return true;
                die();
            }
        }else{
            return false; //未購買
            die();
        }

    }

    /**
     * 计算两个时间戳之间的时间
     */
    protected function formatDate($sTime) {

        //sTime=源时间，cTime=当前时间，dTime=时间差
        $cTime      = time();
        $dTime      = $cTime - $sTime;
        $dDay       = intval(date("Ymd",$cTime)) - intval(date("Ymd",$sTime));
        $dMonth     = intval(date("Ym",$cTime)) - intval(date("Ym",$sTime));
        $dYear      = intval(date("Y",$cTime)) - intval(date("Y",$sTime));

        if($dTime == 0){
            $dTime = '剛剛';
        }elseif( $dTime < 60){
            $dTime =  $dTime."秒前";
        }elseif( $dTime < 3600){
            $dTime =  intval($dTime/60) . "分鐘前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            $dTime =  intval($dTime/3600) . '小時前';
        }elseif ($dDay > 0 && $dMonth == 0) {
            $dTime = $dDay . '天前';
        } elseif ($dMonth > 0 && $dYear == 0) {
            $dTime = $dMonth .'月前';
        } elseif($dYear > 0){
            $dTime =  $dYear . '年前';
        }else{
            $dTime =  date("Y-m-d H:i",$sTime);
        }
        return $dTime;
    }

    /**
     *  把分钟数转换为时分秒的格式
     *  @param Int $min 时间，单位 分钟
     *  @return String
     */
    protected function MinToTime($min) {

        if(!$min) {
            return false;
            die();
        }

        if($min > 0) {
            $hour       = floor($min/60); //小时
            $min        = $min - $hour * 60; //分钟
        }

        return $hour . ':' . $min .':00';
    }

    /**
     * 计算今天的开始及结束时间戳
     */
    protected function todayTime() {

        $beginTime         = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endTime           = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        return array($beginTime,$endTime);
    }

    /**
     * 计算两个时间戳之间相差的时分秒
     * $begin_time  开始时间戳
     * $end_time 结束时间戳
     */
    public function timeDiff($starttime,$endtime)
    {
        if($starttime < $endtime){
            return false;
            die();
        }

        $timediff           = $endtime - $starttime;
        //计算小时数
        $remain             = $timediff % 86400;
        $hours              = intval($remain / 3600);
        //计算分钟数
        $remain             = $remain % 3600;
        $mins               = intval($remain / 60);
        //计算秒数
        $secs               = $remain % 60;

        return array("hour" => $hours,"min" => $mins,"sec" => $secs);
    }
}