<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/12/29
 * Time: 16:10
 */

namespace WebApi_v1\Controller;


class IndexController extends UserBaseController
{
    /*
     * 首页 （轮播图、推荐视频、福利图片、最新小说）
     * 参数 ： 无
     */
    public function index() {

        //轮播图
        $BannerM                        = M('sys_banner_list');
        $SlideShow                      = $BannerM->field('banner_id,name,img,url')->where(array('type' => 0,'state' => 1))->limit(3)->order('sort desc')->select();

        if($SlideShow) {
            foreach ($SlideShow as $k => $v) {
                $SlideShow[$k]['url']   = self::ResourceUrl($v['url']); //链接地址
                $SlideShow[$k]['img']   = self::ResourceUrl($v['img']); //图片地址
            }
        }else{
            $SlideShow                  = array();
        }

        //推荐视频
        $MovieM                         = M('resource_movie');
        $Movie                          = $MovieM->field('movie_id,name,long,movie_url,movie_img')->where(array('state' => 1))->limit(4)->order('push_time desc')->select();

        if($Movie) {
            foreach ($Movie as $k => $v) {
                //$Movie['movie_url']         = "/WebApi_v1/Resource/PlayVideo/movie_id/".$v['movie_id']."/index.m3u8"; //视频播放地址
                $Movie[$k]['movie_img'] = self::ResourceUrl($v['movie_img']); //影片封面图
                $Movie[$k]['long']      = self::MinToTime($v['long']);
            }
        }else{
            $Movie                      = array();
        }

        //福利图片
        $PictureM                       = M('resource_image');
        $Pictures                       = $PictureM->field('image_id,name,long,image_url')->where(array('state' => 1))->limit(5)->order('push_time desc')->select();

        if($Pictures) {
            foreach ($Pictures as $k => $v) {
                $Pictures[$k]['image_url']  = json_decode(self::ResourceUrl($v['image_url']))[0]; //图片地址
            }
        }else{
            $Pictures                   = array();
        }

        //最新小说
        $FictionM                       = M('resource_fiction');
        $Fiction                        = $FictionM->field('fiction_id,name,image_url')->where(array('state' => 1))->limit(8)->order('push_time desc')->select();

        if($Fiction) {
            foreach ($Fiction as $k => $v) {
                $Fiction[$k]['image_url']   = self::ResourceUrl($v['image_url']); //小说封面地址
            }
        }else{
            $Fiction                    = array();
        }

        $data               = array(
            'slideShow'     => $SlideShow,
            'movie'         => $Movie,
            'pictures'      => $Pictures,
            'fiction'       => $Fiction
        );

        self::returnAjax(200, $data);
    }

}