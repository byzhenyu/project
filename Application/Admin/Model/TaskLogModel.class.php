<?php
/**
 * 任务日志模型
 */
namespace Admin\Model;

use Think\Model;

class TaskLogModel extends Model {
    protected $insertFields = array('user_id', 'task_id', 'task_name', 'finish_time');
    protected $updateFields = array();
    protected $_validate = array(
    );

    protected function _before_insert(&$data, $option){
        $data['finish_time'] = NOW_TIME;
        if(!$data['task_name']) $data['task_name'] = D('Admin/Task')->getTaskField(array('id' => $data['task_id']), 'task_name');
        if(!$this->validTaskNumber($data['task_id'])){
            return false;
        }
    }

    /**
     * @desc 检测任务是否还可以完成送令牌
     * @param $task_id int 任务id
     * @return bool
     */
    public function validTaskNumber($task_id){
        $task_info = D('Admin/Task')->getTaskInfo(array('id' => $task_id));
        switch($task_info['type']){
            case 1:
                $start_time = mktime(0,0,0,date('m'), date('d'), date('Y'));
                $end_time = mktime(23,59,59,date('m'),date('d'),date('Y'));
                break;
            default: return true;
        }
        $where = array('finish_time' => array('between', array($start_time, $end_time)), 'task_id' => $task_id);
        $log_number = $this->where($where)->count();
        if($log_number >= $task_info['type_number']) return false;
        return true;
    }
}