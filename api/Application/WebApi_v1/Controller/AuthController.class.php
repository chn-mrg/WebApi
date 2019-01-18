<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/12/30
 * Time: 15:12
 */

namespace WebApi_v1\Controller;

class AuthController extends MainController
{
    /*
     * 用户登錄
     * 參數：
     * phone/email  手机号/邮箱/账号
     * userpwd 密碼
     */
    public function login(){

        $phone              = I('phone');
        $userpwd            = md5(I('userpwd')); //用户密码

        if(!$phone || !$userpwd) {
            self::returnAjax('100005'); //参数错误
        }

        $UserM              = M('user_list'); //实例化

        //多条件查询
        $map                = array();

        $map['userpwd']     = $userpwd;
        $map['_query']      = 'phone=' .$phone. '&email=' .$phone. '&username=' .'&_logic=or';

        $UserInfo           = $UserM->where($map)->find();

        if($UserInfo) {
            //判断用户状态 0-不可發言，1-正常，2-已封號 只有用户未被封号才可以登录成功
            if($UserInfo['state'] != 2) {
                $UserToken   = md5(time() . mt_rand(100000, 99999)); //用户token

                if($UserM->where(array('user_id' => $UserInfo['user_id']))->save(array('user_token' => $UserToken))) { //更新用户token
                    //session存储用户信息
                    $_SESSION['user']   = array(
                        'user_id'      => $UserInfo['user_id'],
                        'user_token'   => $UserToken
                    );
                    self::returnAjax(200, array('user_token' => $UserToken,'user_id'=>$UserInfo['user_id']));
                }

                $_SESSION['user'] = false;
                self::returnAjax(100000); //登录失败(用户token更新失败)
            }

            $_SESSION['user'] = false;
            self::returnAjax(100011); //用户已被封号
        }

        $_SESSION['user'] = false;
        self::returnAjax(100000); //登录失败 账号(邮箱)或密码错误
    }

    /*
     * 退出登錄
     */
    public function outLogin() {
        $_SESSION['user'] = false;
        self::returnAjax(200);
    }

}