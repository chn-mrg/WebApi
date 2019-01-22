<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/12/30
 * Time: 16:37
 */

namespace WebApi_v1\Controller;


class ImageController extends UserBaseController
{
    /*
     * 图片主页面
     * 参数
     * sort 综合排序（1、新上线 2、热播榜 3、好评） 非必填 默认 1
     * category_id 分类id 非必填
     * label_id 标签id 非必填
     * page 当前页数 非必填 默认 1
     */
    public function index() {

        $sort           = I('sort')? I('sort') : 1; //：1、新上线 2、热播榜 3、好评 默認1
        $category_id    = I('category_id');
        $label_id       = I('label_id');
        $num            = 4;

        switch ($sort) {
            case 1: //新上线
                $orderBy = 'push_time DESC';
                break;
            case 2: //热播榜
                $orderBy = 'watch_count DESC';
                break;
            case 3: //好评
                $orderBy = 'score DESC';
                break;
        }

        if($category_id){
            $selectWhere['category_id'] = $category_id;
        }
        if($label_id){
            $selectWhere['label_ids']   = array('like','%"' .$label_id. '"%');
        }
        $selectWhere['state']           = 1;

        $page           = I('page')? I('page') : 1; //当前页

        $imageM         = M('resource_image');


        $imageList      = $imageM->field('image_id,name,score,long,like_count,comment_count,watch_count,image_url')->where($selectWhere)->order($orderBy)->page($page,$num)->select();
        $imageListCount = $imageM->where($selectWhere)->count();

        //获取标签名称
        if($imageList) {
            foreach ($imageList as $k => $v) {
                $imageUrl                       = self::ResourceUrl($v['image_url']);
                $imageList[$k]['image_url']     = json_decode($imageUrl)[0]; //只显示第一张

                //判断该资源是否已被用户收藏
                $userInfo                   = self::getUserInfo();
                if($userInfo) {
                    $isCollection           = self::isCollection($userInfo['user_id'],$v['movie_id'],2);
                    if($isCollection) {
                        $imageList[$k]['isCollection'] = 1; //已收藏
                    }else{
                        $imageList[$k]['isCollection'] = 0; //未收藏
                    }
                }else{
                    $imageList[$k]['isCollection'] = 0; //未收藏
                }
            }
            self::returnAjax(200,array('pages'=>array('count'=>$imageListCount,'num'=>$num),'list'=>$imageList));
        }else{
            self::returnAjax(404);
        }
    }

    /*
     * 图片详情页面
     * 参数
     * image_id 图片id
     */
    public function imageDetail() {

        $imageId            = I('image_id'); //图片id

        if(!$imageId) {
            self::returnAjax(100005); //参数错误
        }

        $imageM             = M('resource_image');

        //图片信息、属性名称
        $imageInfo          = $imageM
                            ->alias('a')
                            ->field('a.image_id,a.category_id,a.label_ids,a.name,a.score,a.long,a.like_count,a.comment_count,a.comment_count,a.image_url, b.name as category_name')
                            ->join('LEFT JOIN sex_sys_category_list b ON b.category_id = a.category_id')
                            ->where(array('a.image_id' => $imageId))
                            ->find();

        if(!$imageInfo) {
            self::returnAjax(404);
        }

        //图片标签
        $labelIds                       = (array)json_decode($imageInfo['label_ids']);
        $imageInfo['labels']            = $this->getLabelArray($labelIds);
        //图集封面图
        $imageInfo['image_url']         = self::ResourceUrl($imageInfo['image_url']);
        //用户是否已收藏该资源
        $userInfo                       = self::getUserInfo();
        if(!$userInfo) {
            $imageInfo['isCollection']  = 0; //未收藏
            $imageInfo['user_id']       = null;
            $imageInfo['head_portrait'] = null;
        }else{
            $imageInfo['user_id']       = $userInfo['user_id'];
            $imageInfo['head_portrait'] = self::ResourceUrl($userInfo['head_portrait']);
            $isCollection                   = self::isCollection($userInfo['user_id'],$imageInfo['image_id'],2);
            if($isCollection) {
                $imageInfo['isCollection']  = 1; //已收藏
            }else{
                $imageInfo['isCollection']  = 0; //未收藏
            }
        }

        self::returnAjax(200,$imageInfo);
    }

    /*
     * 图片详情---查看图片
     * 参数
     * image_id 图片id
     * page 当前页 非必填 默认 1
     */
    public function reviewImage() {

        $image_id           = I('image_id'); //图片id
        $page               = I('page')? I('page') : 1;

        if(!$image_id) {
            self::returnAjax(100005);
        }

        $imageM             = M('resource_image');

        $imageInfo          = $imageM->field('image_id,name,long,like_count,comment_count,image_url')->where(array('image_id' => $image_id))->find();

        if(!$imageInfo) {
            self::returnAjax(404);
        }

        //用户是否已收藏该资源
        $userInfo                       = self::getUserInfo();
        if(!$userInfo) {
            $imageInfo['isCollection']  = 0; //未收藏
        }else{
            $isCollection                   = self::isCollection($userInfo['user_id'],$imageInfo['image_id'],2);
            if($isCollection) {
                $imageInfo['isCollection']  = 1; //已收藏
            }else{
                $imageInfo['isCollection']  = 0; //未收藏
            }
        }
        //图片地址
        $image_url           = self::ResourceUrl($imageInfo['image_url']);
        $imageInfo['image_url'] = json_decode($image_url)[$page - 1];

        self::returnAjax(200,$imageInfo);
    }
}