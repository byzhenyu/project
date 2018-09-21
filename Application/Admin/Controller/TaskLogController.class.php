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
        $model = D('Admin/TaskLog');
        $where = array();
        if($keyword){
            $where['l.task_name'] = array('like', '%'. $keyword .'%');
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
        $this->_del('Task', 'id');
    }
}