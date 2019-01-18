<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2019/1/19
 * Time: 上午 12:38
 */

namespace WebApi_v1\Controller;


class UserTaskController extends UserBaseController
{
    /*
     * 任务中心
     */
    public function task() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }

        //所有任务
        $sysTaskM           = M('sys_task');
        $taskList           = $sysTaskM->select();
        if(!$taskList) {
            self::returnAjax(404);
        }

        foreach ($taskList as $k => $v) {
            $taskM           = M('record_task');
            $recordTask      = $taskM->where(array('task_id'=>$v['task_id'],'user_id'=>$userInfo['user_id']))->find();
            if($recordTask) {
                //未达到 需完成任务数
                if($recordTask['state'] == 0 && $recordTask['num'] < $v['num']){
                    $taskList[$k]['state']  = 0; //未完成
                }
                if($recordTask['state'] == 1) {$taskList[$k]['state'] = 1;} //（领取）任务已完成 但未领取任务
                if($recordTask['state'] == 2) {$taskList[$k]['state'] = 2;} //已完成
            }else{
                $taskList[$k]['state']      = 0; //未完成
            }
        }

        self::returnAjax(200, $taskList);
    }

    /*
     * 领取任务奖励
     * 参数
     * task_id 任务id
     * experience 任务经验
     * reward 资源券
     * gold G点
     */
    public function getReward() {

        $userInfo           = self::getUserInfo();
        if(!$userInfo) {
            self::returnAjax(100012);
        }
        $task_id            = I('task_id'); //任务id
        if(!$task_id) {
            self::returnAjax(100005);
        }

        //修改任务记录状态
        $taskM              = M('record_task');
        $data['status']     = 2; //已完成
        $data['get_time']   = time(); //领取时间
        $result             = $taskM->where(array('task_id'=>$task_id,'user_id'=>$userInfo['user_id']))->save($data);

        if($result) {
            //查询该任务所有奖励
            $sysTaskM           = M('sys_task');
            $sysTaskInfo        = $sysTaskM->where(array('task_id'=>$task_id))->find();

            $experience         = $sysTaskInfo['experience']; //经验
            $reward             = $sysTaskInfo['reward']; //资源券
            $gold               = $sysTaskInfo['gold']; //G点

            //增加经验
            if($experience > 0) {
                //自增用户表经验值
                M('user_list')->where(array('user_id'=>$userInfo['user_id']))->setInc('experience',$experience);
                //添加经验记录
                $expData        = array(
                    'user_id'   => $userInfo['user_id'],
                    'experience'=> $experience,
                    'type'      => 1, //收入
                    'time'      => time(),
                    'memo'      => '完成任务增加经验'
                );
                M('user_experience')->add($expData); //添加记录
            }
            //奖励资源券
            if($reward > 0) {
                //自增用户表资源券数
                M('user_list')->where(array('user_id'=>$userInfo['user_id']))->setInc('watch',$reward);
                //添加资源券记录
                $watchData      = array(
                    'user_id'   => $userInfo['user_id'],
                    'watch'     => $reward,
                    'type'      => 1, //收入
                    'time'      => time(),
                    'memo'      => '完成任务奖励'
                );
                M('user_watch')->add($watchData); //添加记录
            }
            //奖励G点
            if($gold > 0) {
                //自增用户表G点数
                M('user_list')->where(array('user_id'=>$userInfo['user_id']))->setInc('money',$gold);
                //添加G点记录
                $moneyData      = array(
                    'user_id'   => $userInfo['user_id'],
                    'money'     => $gold,
                    'type'      => 1, //收入
                    'time'      => time(),
                    'memo'      => '完成任务奖励'
                );
                M('user_money')->add($moneyData); //添加记录
            }

            self::returnAjax(200);
        }else{
            self::returnAjax(301);
        }
    }
}