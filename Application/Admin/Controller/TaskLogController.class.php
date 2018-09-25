<?php
/**
 * 任务完成情况
 */
namespace Admin\Controller;
use Think\Controller;
class TaskLogController extends CommonController {


    //任务列表
    public function listTaskLog(){
        $keyword = I('keyword', '', 'trim');
        $start = I('min', '', 'trim');
        $end = I('max', '', 'trim');
        $model = D('Admin/TaskLog');
        $where = array();
        if($keyword){
            $where['l.task_name'] = array('like', '%'. $keyword .'%');
        }
        if($start && $end){
            $start = strtotime($start.' 00:00:00');
            $end = strtotime($end.' 23:59:59');
            $where['l.finish_time'] = array('between', array($start, $end));
        }
        $list = $model->getTaskLogList($where);
        foreach($list['info'] as &$val){
            $val['user_name'] = !empty($val['nickname']) ? $val['nickname'] : $val['user_name'];
        }
        unset($val);
        $this->keyword = $keyword;
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    public function del(){
        $this->_del('TaskLog', 'id');
    }
}