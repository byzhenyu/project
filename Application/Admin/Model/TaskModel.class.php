<?php
/**
 * 任务模型
 */
namespace Admin\Model;

use Think\Model;

class TaskModel extends Model {
    protected $insertFields = array('task_name', 'reward', 'task_desc', 'type', 'task_url', 'type_number', 'sort', 'task_icon');
    protected $updateFields = array('task_name', 'reward', 'task_desc', 'type', 'task_url', 'type_number', 'id', 'sort', 'task_icon');
    protected $_validate = array(
        array('task_name', 'require', '任务名称不能为空！', 1, 'regex', 3),
        array('task_name', '1,100', '任务名称长度不能超过100！', 1, 'length', 3),
        array('reward', 'require', '奖励令牌数量不能为空！', 1, 'regex', 3),
        array('task_desc', 'require', '任务描述不能为空！', 1, 'regex', 3),
        array('task_desc', '1,100', '任务描述长度不能超过100！', 1, 'length', 3),
        //array('task_url', 'require', '任务链接不能为空！', 1, 'regex', 3),
        array('type_number', 'require', '可领任务数量不能为空！', 1, 'regex', 3),
        array('type', 'require', '请选择任务类型', 1, 'regex', 3),
        array('sort', 'require', '排序序号不能为空', 1, 'regex', 3),
        array('task_icon', 'require', '任务图标不能为空', 1, 'regex', 3)
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
        $data['reward'] = yuan_to_fen($data['reward']);
    }

    protected function _before_update(&$data, $option){
        $data['reward'] = yuan_to_fen($data['reward']);
    }
    /**
     *  获取任务列表及完成情况
     */
    public function getTaskList() {
        $info = $this->order('sort asc')->select();
        $TaskLogModel = D('Admin/TaskLog');
        foreach ($info as $k=>$v) {
            $info[$k]['can'] = $TaskLogModel->validTaskNumber($v['id']);
        }
        return $info;
    }
}