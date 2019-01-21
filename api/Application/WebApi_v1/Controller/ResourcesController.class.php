<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/12/30
 * Time: 16:04
 */

namespace WebApi_v1\Controller;


use WebApi_v1\Common\Tool\BuyResourceController;
use WebApi_v1\Common\Tool\CollectionToolController;
use WebApi_v1\Common\Tool\CommentToolController;

class ResourcesController extends UserBaseController
{
    /*
    * 一级分类 （激情视频、性感图片、春情小说）
    * 参数 无
    */
    public function getFirstCateGory() {

        $CateGoryM          = M('sys_category_list');

        $CateGoryInfo       = $CateGoryM->cache((C('cacheName'))['FirstCategoryCache'])->where(array('parent_id' => 0,'state' => 1))->select();

        if(!$CateGoryInfo) {
            self::returnAjax(301); //失败
        }

        foreach ($CateGoryInfo as $k => $v) {
            $CateGoryInfo[$k]['icon'] = self::ResourceUrl($v['icon']); //图标地址
        }

        self::returnAjax(200, $CateGoryInfo);
    }

    /*
     * 全部资源二级分类
     * 参数
     * parent_id 分类id 1、视频 2、图片 3、小说
     */
    public function getCateGory()
    {
        $parent_id          = I('parent_id'); //父级分类id

        if(!$parent_id) {
            self::returnAjax(100005); //无效参数
        }

        $CateGoryM          = M('sys_category_list');

        switch ($parent_id) {
            case 1:
                $CateGory   = $CateGoryM->cache((C('cacheName'))['MovieCategoryCache'])->where(array('parent_id' => $parent_id, 'state' => 1))->select();
                break;
            case 2:
                $CateGory   = $CateGoryM->cache((C('cacheName'))['ImageCategoryCache'])->where(array('parent_id' => $parent_id, 'state' => 1))->select();
                break;
            case 3:
                $CateGory   = $CateGoryM->cache((C('cacheName'))['FictionCategoryCache'])->where(array('parent_id' => $parent_id, 'state' => 1))->select();
                break;
        }

        if(!$CateGory) {
            self::returnAjax(301);
        }

        self::returnAjax(200, $CateGory);
    }

    /*
     * 全部标签
     * 参数 无
     */
    public function getLabel() {

        $sysLabelListM  = M('sys_label_list');

        $LabelList      = $sysLabelListM->cache((C('cacheName'))['SysLabelCache'])->select();

        if(!$LabelList) {
            self::returnAjax(301);
        }

        self::returnAjax(200, $LabelList);
    }

    /*
     * 搜索(电影、图片、小说）
     * 参数
     * 类型： parent_id 1、电影 2、图片 3、小说
     * 视频名称： name
     */
    public function getSearch() {

        $parent_id      = I('parent_id'); //分类
        $name           = I('name'); //影片名称

        if(!$name || !$parent_id) {
            self::returnAjax(100005); //参数错误
        }

        switch ($parent_id) {
            case 1:
                $M      = M('resource_movie'); //电影
                break;
            case 2:
                $M      = M('resource_image'); //图片
                break;
            case 3:
                $M      = M('resource_fiction'); //小说
                break;
        }

        $Where['name']  = array("LIKE", '%' . $name . '%'); //根据名称模糊查询

        $MovieInfo      = $M->where($Where)->select();

        if(!$MovieInfo) {
            self::returnAjax(301); //无数值
        }

        foreach ($MovieInfo as $k => $v) {

        }

        self::returnAjax(200, $MovieInfo);
    }

    /*
     * 资源详情页获取评论及回复
     * 参数
     * type 评论类型 0-社區動態，1-視頻，2-圖片，3-小説
     * resource_id 资源id
     * page 当前页 非必填 默认 第1页
     */
    public function getCommentReply() {

        $type           = I('type');
        $resource_id    = I('resource_id');
        $page           = I('page')? I('page') : 1;

        if(!$resource_id || !is_numeric($type)) {
            self::returnAjax(100005);
        }

        $commentReply   = self::commentInfo($type, $resource_id, $page);

        if(!$commentReply) {
            self::returnAjax(404);
        }

        self::returnAjax(200, $commentReply);
    }

    /*
     * 查看全部回复
     * comment_id 评论id
     * page 当前页 非必填 默认 第1页
     */
    public function allReply() {

        $comment_id             = I('comment_id');
        $page                   = I('page')? I('page') : 1;
        if(!$comment_id) {
            self::returnAjax(100005);
        }

        $data                   = self::getAllReply($comment_id,$page);

        if(!$data) {
            self::returnAjax(404);
        }

        self::returnAjax(200, $data);
    }

    /*
    * 评论
    * 参数
    * type 类型（0-社區動態，1-視頻，2-圖片，3-小説）
    * resource_id 资源id
    * comment 评论内容
    */
    public function commentOn() {

        $userInfo           = self::getUserInfo();

        if(!$userInfo) {
            self::returnAjax(100012); //此操作须用户先登录
        }

        $type               = I('type'); //类型（0-社區動態，1-視頻，2-圖片，3-小説）
        $resource_id        = I('resource_id'); //资源id
        $comment            = I('comment'); //评论内容

        if(!is_numeric($type) || !$resource_id || !$comment) {
            self::returnAjax(100005);
        }

        //評論字數限制最大250
        if(self::abslength($comment) > 250){
            self::returnAjax(100015);
        }

        $result             = CommentToolController::comment($userInfo['user_id'], $type,$resource_id,$comment);

        if(!$result) {
            self::returnAjax(301);
        }

        self::returnAjax(200);
    }

    /*
     * 回复评论、回复回复
     * 参数
     * parent_id 非必填 默认为0 （为0时是回复评论）
     * comment_id 评论id
     * reply 回复内容
     */
    public function replyOn() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012); //此操作须用户先登录
        }

        $parent_id          = I('parent_id')? I('parent_id') : 0;
        $comment_id         = I('comment_id'); //评论id
        $reply              = I('reply'); //回复内容

        if(!$comment_id || !$reply || !is_numeric($parent_id)) {
            self::returnAjax(100005);
        }

        //評論字數限制最大250
        if(self::abslength($reply) > 100){
            self::returnAjax(100016);
        }

        $result             = CommentToolController::reply($userInfo['user_id'],$parent_id,$comment_id,$reply);

        if(!$result) {
            self::returnAjax(301);
        }

        self::returnAjax(200);
    }

    /*
     * 收藏
     * 参数
     * type 類型 1-視頻，2-圖集，3-小説
     * resource_id 资源id
     */
    public function collection() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012); //此操作需用户先登录
        }

        $type               = I('type'); //類型 1-視頻，2-圖集，3-小説
        $resource_id        = I('resource_id'); //资源id
        if(!$type || !$resource_id) {
            self::returnAjax(100005);
        }

        $like_id             = CollectionToolController::collect($userInfo['user_id'],$type,$resource_id);

        if(!$like_id) {
            self::returnAjax(301);
        }

        self::returnAjax(200,$like_id);
    }

    /*
     * 取消收藏
     * 参数
     * type 類型 1-視頻，2-圖集，3-小説
     * like_id 收藏id
     * resource_id 资源id
     */
    public function cancelCollect() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012); //此操作需用户先登录
        }

        $type               = I('type');
        $resource_id        = I('resource_id');
        if(!$type || !$resource_id) {
            self::returnAjax(100005);
        }

        $result             = CollectionToolController::cancel($userInfo['user_id'],$type,$resource_id);

        if(!$result) {
            self::returnAjax(301);
        }

        self::returnAjax(200);
    }

    /*
     * 資源購買
     * type 資源類型 1-視頻 2-圖片 3-小説
     * pey_type 購買方式 0-資源券購買 1-G點購買
     * resource_id 資源id
     */
    public function BuyResource(){

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }

        $type               = I('type');
        $pey_type           = I('pey_type');
        $resource_id        = I('resource_id');
        if(!$type || !is_numeric($pey_type) || !$resource_id) {
            self::returnAjax(100005);
        }

        //查詢購買資源花費
        if($type == 1) {
            $resourcesM             = M('resource_movie');
            $where['movie_id']      = $resource_id;
        }
        if($type == 2) {
            $resourcesM             = M('resource_image');
            $where['image_id']      = $resource_id;
        }
        if($type == 3) {
            $resourcesM             = M('resource_fiction');
            $where['fiction_id']    = $resource_id;
        }
        $resourceInfo               = $resourcesM->where($where)->find();

        if($resourceInfo) {
            //根據支付方式 判斷用戶餘額
            if($pey_type == 0) { //資源券
                if($userInfo['watch'] >= $resourceInfo['watch_count']){
                    $result             = BuyResourceController::mode($userInfo['user_id'], $type, $resource_id, $pey_type, $resourceInfo['watch_count']);
                    if($result) {
                        self::returnAjax(200);
                    }else{
                        self::returnAjax(301);
                    }
                }else{
                    self::returnAjax(100018);
                }
            }

            if($pey_type == 1) { //G
                if($userInfo['money'] >= $resourceInfo['money']) {
                    $result             = BuyResourceController::mode($userInfo['user_id'], $type, $resource_id, $pey_type, $resourceInfo['money']);
                    if($result) {
                        self::returnAjax(200);
                    }else{
                        self::returnAjax(301);
                    }
                }else{
                    self::returnAjax(100017);
                }
            }

        }else{
            self::returnAjax(301);
        }
    }

    /*
     * 资源转发
     */
    public function forwarding() {

    }
}