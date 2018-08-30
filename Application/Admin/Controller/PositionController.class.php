<?php
/**
 * 职位管理类
 */
namespace Admin\Controller;
use Think\Controller;
class PositionController extends CommonController {

    //职位添加/编辑
    public function editPosition(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/Position');
        if(IS_POST){
            $data = I('post.');
            if ($id > 0){
                if($model->create($data, 2)){
                    if ($model->where(array('id' => $id))->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                }
            } else {
                if($model->create($data, 1)){
                    if ($model->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }

            $this->ajaxReturn(V(0, $model->getError()));
        } 
        $contraband = M('Position')->find($id);
        $this->assign('info', $contraband);
        $this->display();
    }

    //显示职位列表
    public function listPosition(){
        $keyword = I('keyword', '', 'trim');

        $model = D('Admin/Position');
        $where = array();
        //关键字查询
        if($keyword){
            $where['position_name'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getPositionList($where);


        $this->keyword = $keyword;
        $this->assign('list', $list['info']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    // 放入回收站
    public function del(){
        $this->_del('Position');//调用父类的方法
    }
}