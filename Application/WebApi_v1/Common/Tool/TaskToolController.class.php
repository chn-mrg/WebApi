<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/3
 * Time: 10:52
 */

namespace WebApi_v1\Common\Tool;


class TaskToolController
{
    /*
     * 任务事件
     */
    protected static $user_id; //用户id

    /*
     * 新手任务
     * 1、注册账户 + 10资源卷
     * 2、绑定邮箱 + 3 资源卷
     * 3、绑定手机 + 3 资源卷
     */

    /*
     * 进阶任务
     * 4、邀请用户 + 3 资源卷
     * 5、发布动态 根据动态质量 管理员奖励金币
     */

    /*
     * 每日任务
     * 6、发布动态 +3 资源卷 一次
     * 7、评论动态 +1 资源卷 一次
     * 8、签到 根据等级赠送资源卷 3 * 阶段（3 * 2的n-1次方）
     */

    //----------------新手任务验证--------------
    protected static function noviceTaskCheck() {

    }



    //----------------每日任务验证--------------
    protected static function dailyTaskCheck() {

    }
}