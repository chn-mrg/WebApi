<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/12/30
 * Time: 20:20
 */

namespace WebApi_v1\Controller;


class FictionController extends UserBaseController
{
    /*
     * 小说主页面
     * 参数
     * sort 综合排序（1、新上线 2、热播榜 3、好评） 非必填 默认 1
     * category_id 分类id 非必填
     * label_id 标签id 非必填
     * page 当前页数 非必填 默认 1
     */
    public function index() {

        $sort               = I('sort')? I('sort') : 0; //0-综合排序(默认 0)：1、新上线 2、热播榜 3、好评
        $category_id        = I('category_id');
        $label_id           = I('label_id');
        $num                = 9;

        switch ($sort) {
            case 0:
                $orderBy = 'rand()';
                break;
            case 1: //新上线
                $orderBy    = 'push_time DESC';
                break;
            case 2: //热播榜
                $orderBy    = 'watch_count DESC';
                break;
            case 3: //好评
                $orderBy    = 'score DESC';
                break;
        }

        if($category_id){
            $selectWhere['category_id'] = $category_id;
        }
        if($label_id){
            $selectWhere['label_ids']   = array('like','%"' .$label_id. '"%');
        }
        $selectWhere['state']           = 1;

        $page               = I('page')? I('page') : 1; //当前页

        $fictionM           = M('resource_fiction');

        $fictionList        = $fictionM->field('fiction_id,name,image_url')->where($selectWhere)->order($orderBy)->page($page,$num)->select();
        $fictionListCount   = $fictionM->where($selectWhere)->count();

        if($fictionList) {
            foreach ($fictionList as $k => $v) {
                //小说封面图
                $fictionList[$k]['image_url']     = self::ResourceUrl($v['image_url']);
                //判断该资源是否已被用户收藏
                $userInfo                   = self::getUserInfo();
                if($userInfo) {
                    $isCollection           = self::isCollection($userInfo['user_id'],$v['movie_id'],3);
                    if($isCollection) {
                        $fictionList[$k]['isCollection'] = 1; //已收藏
                    }else{
                        $fictionList[$k]['isCollection'] = 0; //未收藏
                    }
                }else{
                    $fictionList[$k]['isCollection'] = 0; //未收藏
                }
            }
            self::returnAjax(200,array('pages'=>array('count'=>$fictionListCount,'num'=>$num),'list'=>$fictionList));
        }else{
            self::returnAjax(404);
        }
    }

    /*
     * 小说详情页
     * 参数
     * fiction_id 小说id
     */
    public function fictionDetail() {

        $fiction_id             = I('fiction_id'); //小说id
        if(!$fiction_id) {
            self::returnAjax(100005);
        }

        $fictionM               = M('resource_fiction');

        $fictionInfo            = $fictionM
                                ->alias('a')
                                ->field('a.fiction_id,a.category_id,a.label_ids,a.name,a.score,a.like_count,a.comment_count,a.watch_count,a.money,a.essay, b.name as category_name')
                                ->join('LEFT JOIN sex_sys_category_list b ON b.category_id = a.category_id')
                                ->where(array('fiction_id' => $fiction_id))
                                ->find();

        if(!$fictionInfo) {
            self::returnAjax(404);
        }

        //小说标签
        $labelIds                = (array)json_decode($fictionInfo['label_ids']);
        $fictionInfo['labels']   = $this->getLabelArray($labelIds);
        //判断该资源是否已被用户收藏
        $userInfo                = self::getUserInfo();
        if($userInfo) {
            $fictionInfo['user_id']       = $userInfo['user_id'];
            $fictionInfo['head_portrait'] = self::ResourceUrl($userInfo['head_portrait']);
            $isCollection        = self::isCollection($userInfo['user_id'],$fictionInfo['movie_id'],3);
            if($isCollection) {
                $fictionInfo['isCollection'] = 1; //已收藏
            }else{
                $fictionInfo['isCollection'] = 0; //未收藏
            }
        }else{
            $fictionInfo['isCollection'] = 0; //未收藏
            $fictionInfo['user_id']       = null;
            $fictionInfo['head_portrait'] = null;
        }

        self::returnAjax(200, $fictionInfo);
    }
}