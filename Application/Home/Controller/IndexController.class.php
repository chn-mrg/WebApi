<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        echo "歡迎來到本頁面，招聘郵箱：thmrsix@gmail.com";
        //$this->ajaxReturn(array('code'=>404,'msg'=>'歡迎來到本頁面'),'JSON');
    }
}