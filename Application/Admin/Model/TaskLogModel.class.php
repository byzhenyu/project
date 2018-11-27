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

    /**
     * @desc 任务完成日志
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getTaskLogList($where, $field = false, $order = 'l.finish_time desc'){
        if(!$field) $field = 'l.*,u.nickname,u.mobile,u.user_name';
        $number = $this->alias('l')->join('__USER__ as u on l.user_id = u.user_id')->where($where)->count();
        $page = get_web_page($number);
        $list = $this->alias('l')->join('__USER__ as u on l.user_id = u.user_id')->where($where)->field($field)->limit($page['limit'])->order($order)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    protected function _before_insert(&$data, $option){
        $valid_update_time = $data['update_time'];
        unset($data['update_time']);
        $data['finish_time'] = NOW_TIME;
        if(!$data['task_name']) $data['task_name'] = D('Admin/Task')->getTaskField(array('id' => $data['task_id']), 'task_name');
        if(!$this->validTaskNumber($data['task_id'], $data['user_id'], false, $valid_update_time)){
            return false;
        }
    }

    /**
     * @desc 检测任务是否还可以完成送令牌
     * @param $task_id int 任务id
     * @param $user_id
     * @param $return_number bool 是否反馈已完成数量
     * @param $update_time bool|int 简历最后修改时间[后台审核HR上传简历时任务完成情况验证字段]
     * @return bool
     */
    public function validTaskNumber($task_id, $user_id = UID, $return_number = false, $update_time = false){
        $task_info = D('Admin/Task')->getTaskInfo(array('id' => $task_id));
        if(!$update_time) $update_time = time();
        $_month = date('m', $update_time);
        $_day = date('d', $update_time);
        $_week = date('w', $update_time);
        $_year = date('Y', $update_time);
        $_t = date('t', $update_time);
        switch($task_info['type']){
            case 1://本日
                $start_time = mktime(0,0,0,$_month, $_day, $_year);
                $end_time = mktime(23,59,59,$_month,$_day,$_year);
                break;
            case 2://本周
                $date_w = $_week;
                if($date_w == 0) $date_w = 7;
                $start_time = mktime(0,0,0,$_month,$_day-$date_w+1,$_year);
                $end_time = mktime(23,59,59,$_month,$_day-$date_w+7,$_year);
                break;
            case 3://本月
                $start_time = mktime(0,0,0,$_month,1,$_year);
                $end_time = mktime(23,59,59,$_month,$_t,$_year);
                break;
            case 0:
                $start_time = 0;
                $end_time = 0;
                break;
            default: return true;
        }
        $where = array('finish_time' => array('between', array($start_time, $end_time)), 'task_id' => $task_id, 'user_id' => $user_id);
        if($task_info['type'] == 0) unset($where['finish_time']);//永久限制任务
        $log_number = $this->where($where)->count();
        if($return_number) return $log_number;
        if($log_number >= $task_info['type_number']) return false;
        return true;
    }
}