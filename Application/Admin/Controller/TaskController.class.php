<?php
/**
 * 任务管理控制器
 */
namespace Admin\Controller;
use Think\Controller;
class TaskController extends CommonController {

    //任务添加/编辑
    public function editTask(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/Task');
        if(IS_POST){
            $data = I('post.');
            if ($id > 0){
                if($model->create($data, 2)){
                    if ($model->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                    else{
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
            }
            else{
                if($model->create($data, 1)){
                    if ($model->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $model->getError()));
        }
        $task = $model->getTaskInfo(array('id' => $id));
        $this->info = $task;
        $this->display();
    }

    //任务列表
    public function listTask(){
        $keyword = I('keyword', '', 'trim');
        $model = D('Admin/Task');
        $where = array();
        if($keyword){
            $where['task_name'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getTaskList($where);
        $this->keyword = $keyword;
        $this->list = $list;
        $this->display();
    }

    public function del(){
        $id = I('id');
        $id = explode(',', $id);
        if(in_array(1, $id) || in_array(2, $id) || in_array(3, $id)) $this->ajaxReturn(V(0, '删除任务中包含不可删除的任务！'));
        $this->_del('Task', 'id');
    }
}