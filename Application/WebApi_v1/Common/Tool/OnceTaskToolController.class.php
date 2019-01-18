<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/18
 * Time: 下午 02:39
 */

namespace WebApi_v1\Common\Tool;


class OnceTaskToolController
{
    /**
     * 任务事件
     */

    protected static $user_id; //用户id
    protected static $type; //任务类型 （1-新手任务 2-每日任务 3-进阶任务）
    protected static $task_id; //任务事件

    /**
     * 新手任务
     */
    public static function once($user_id, $task_id, $type) {

        self::$user_id      = $user_id;
        self::$task_id      = $task_id;
        self::$type         = $type;

        if (!self::$task_id) {
            return false;
        }

    }

    //此任务是否已完成
    protected static function isTask() {

        $taskM          = M('record_task');

        $result         = $taskM->where(array('user_id'=>self::$user_id,'task_id'=>self::$task_id))->find();

        if($result) {
            return true;
        }else{
            return false;
        }
    }

    //根据任务id与任务类型获取该任务信息
    protected static function event($task_id, $type) {

        $taskM              = M('sys_task');

        $taskInfo           = $taskM->where(array('task_id'=>$task_id,'type'=>$type))->find();

        return $taskInfo;
    }


    /**
     * 获取所有任务事件及相对应奖励
     */
    protected static function task() {

        if(!self::$type) {
            return false;
            die();
        }
        $taskM              = M('sys_task');

        $taskList           = $taskM->where(array('type'=>self::$type))->select();

        if($taskList) {
            return $taskList;
        }else{
            return false;
            die();
        }
    }
}