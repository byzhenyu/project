<?php
/**
 * 任务模型
 */
namespace Admin\Model;

use Think\Model;

class TaskModel extends Model {
    protected $insertFields = array('task_name', 'reward', 'task_desc', 'type', 'task_url', 'type_number');
    protected $updateFields = array();
    protected $_validate = array(
        array('task_name', 'require', '任务名称不能为空！', 1, 'regex', 3),
        array('task_name', '1,100', '任务名称长度不能超过100！', 1, 'length', 3),
        array('reward', 'require', '奖励令牌数量不能为空！', 1, 'regex', 3),
        array('task_desc', 'require', '任务描述不能为空！', 1, 'regex', 3),
        array('task_desc', '1,100', '任务描述长度不能超过100！', 'length', 3),
        array('task_url', 'require', '任务链接不能为空！', 1, 'regex', 3),
        array('type_number', 'require', '可领任务数量不能为空！', 1, 'regex', 3)
    );

    /**
     * @desc 获取任务详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getTaskInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    /**
     * @desc 获取任务字段名称
     * @param $where
     * @param $field
     * @return mixed
     */
    public function getTaskField($where, $field){
        $res = $this->where($where)->getField($field);
        return $res;
    }

    protected function _before_insert(&$data, $option){
    }
}