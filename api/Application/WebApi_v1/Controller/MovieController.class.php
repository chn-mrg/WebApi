<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/12/30
 * Time: 15:51
 */

namespace WebApi_v1\Controller;


class MovieController extends UserBaseController
{
    /*
     * 电影主页面
     * 参数
     * sort 综合排序（1、新上线 2、热播榜 3、好评） 非必填 默认 1
     * category_id 分类id 非必填
     * label_id 标签id 非必填
     * page 当前页数 非必填 默认 1
     */
    public function index() {

        $sort           = I('sort')? I('sort') : 0; //0-综合排序(默认 0)：1、新上线 2、热播榜 3、好评
        $category_id    = I('category_id');
        $label_id       = I('label_id');
        $num            = 4;

        switch ($sort) {
            case 0:
                $orderBy = 'rand()';
                break;
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

        $movieM         = M('resource_movie');


        $movieList      = $movieM->field('movie_id,name,score,long,like_count,comment_count,watch_count,movie_img')->where($selectWhere)->order($orderBy)->page($page,$num)->select();
        $movieListCount = $movieM->where($selectWhere)->count();

        if($movieList) {
            foreach ($movieList as $k => $v) {
                //影片地址、封面图地址
                //$movieList[$k]['movie_url'] = "/Api_v1/Resource/PlayVideo/movie_id/".$v['movie_id']."/index.m3u8";
                $movieList[$k]['movie_img'] = self::ResourceUrl($v['movie_img']);
                //时长转换
                $movieList[$k]['long']      = self::MinToTime($v['long']);

                //判断该资源是否已被用户收藏
                $userInfo                   = self::getUserInfo();
                if($userInfo) {
                    $isCollection           = self::isCollection($userInfo['user_id'],$v['movie_id'],1);
                    if($isCollection) {
                        $movieList[$k]['isCollection'] = 1; //已收藏
                    }else{
                        $movieList[$k]['isCollection'] = 0; //未收藏
                    }
                }else{
                    $movieList[$k]['isCollection'] = 0; //未收藏
                }
            }
            self::returnAjax(200,array('pages'=>array('count'=>$movieListCount,'num'=>$num),'list'=>$movieList));
        }else{
            self::returnAjax(404);
        }
    }

    /*
     * 影片详情
     * movie_id 影片id
     */
    public function movieDetail() {

        $movie_id           = I('movie_id');
        if(!$movie_id) {
            self::returnAjax(100005);
        }

        $movieM             = M('resource_movie');

        $movieInfo          = $movieM
                            ->alias('a')
                            ->field('a.movie_id,a.category_id,a.label_ids,a.name,a.score,a.long,a.like_count,a.comment_count,a.comment_count,a.movie_img,a.introduction,a.money, b.name as category_name')
                            ->join('LEFT JOIN sex_sys_category_list b ON b.category_id = a.category_id')
                            ->where(array('a.movie_id' => $movie_id))
                            ->find();

        if(!$movieInfo) {
            self::returnAjax(404);
        }

        //标签
        $labelIds                       = (array)json_decode($movieInfo['label_ids']);
        $movieInfo['labels']            = $this->getLabelArray($labelIds);
        //时长转换
        $movieInfo['long']              = self::MinToTime($movieInfo['long']);
        //地址
        $movieInfo['movie_img']         = self::ResourceUrl($movieInfo['movie_img']);
        //判断该资源是否已被用户收藏
        $userInfo                       = self::getUserInfo();
        if($userInfo) {
            $movieInfo['user_id']       = $userInfo['user_id'];
            $movieInfo['head_portrait'] = self::ResourceUrl($userInfo['head_portrait']);
            $isCollection               = self::isCollection($userInfo['user_id'],$movieInfo['movie_id'],1);
            if($isCollection) {
                $movieInfo['isCollection'] = 1; //已收藏
            }else{
                $movieInfo['isCollection'] = 0; //未收藏
            }
        }else{
            $movieInfo['isCollection']  = 0; //未收藏
            $movieInfo['user_id']       = null;
            $movieInfo['head_portrait'] = null;
        }


        self::returnAjax(200, $movieInfo);
    }

    /*
     * 播放影片縮略
     */
    public function PlayAbbreviationVideo(){
        $movie_id           = I('movie_id');
        if(!$movie_id) {
            self::returnAjax(100005);
        }
        $movieM         = M('resource_movie');
        $movie_info = $movieM->field('movie_url')->where(array('movie_id'=>$movie_id))->find();
        if(!$movie_info){
            self::returnAjax(100005);
        }

        $json = self::GetRequest(self::ResourceUrl($movie_info['movie_url']));
        $request = (array)json_decode($json);
        $num = count($request['ts_list']);
        $numran = $num > 5 ? floor($num / 5) : 1;
        $array = array(
            'targetduration' => $request['targetduration'],
        );
        if($request['ts_list'][($numran)]){
            $array['ts_list'][] = $request['ts_list'][($numran)];
        }
        if($request['ts_list'][($numran*2)]){
            $array['ts_list'][] = $request['ts_list'][($numran*2)];
        }
        if($request['ts_list'][($numran*3)]){
            $array['ts_list'][] = $request['ts_list'][($numran * 3)];
        }
        if($request['ts_list'][($numran*4)]){
            $array['ts_list'][] = $request['ts_list'][($numran * 4)];
        }
        $m3u8 = self::ArrayToM3u8($array);
        if ($m3u8) {
            header('Content-type: application/vnd.apple.mpegurl');
            echo $m3u8;
            die();
        }
        echo "404";
        die();
    }


    /*
     * 播放完整影片
     */
    public function PlayVideo(){
        $movie_id           = I('movie_id');
        if(!$movie_id) {
            self::returnAjax(100005);
        }
        $movieM         = M('resource_movie');
        $movie_info = $movieM->field('movie_url')->where(array('movie_id'=>$movie_id))->find();
        if(!$movie_info){
            self::returnAjax(100005);
        }

        $json = self::GetRequest(self::ResourceUrl($movie_info['movie_url']));
        $request = (array)json_decode($json);
        $m3u8 = self::ArrayToM3u8($request);
        if ($m3u8) {
            header('Content-type: application/vnd.apple.mpegurl');
            echo $m3u8;
            die();
        }
        echo "404";
        die();
    }

}