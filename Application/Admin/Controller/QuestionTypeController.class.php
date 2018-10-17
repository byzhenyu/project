<?php
/**
 * 问题类型
 */
namespace Admin\Controller;
use Think\Controller;
class QuestionTypeController extends CommonController {

    //问题类型添加/编辑
    public function editQuestion(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/QuestionType');
        if(IS_POST){
            $data = I('post.');
            if ($id > 0){
                if($model->create($data, 2)){
                    if ($model->save() !== false) {
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
        $contraband = M('QuestionType')->find($id);
        $this->assign('info', $contraband);
        $this->display();
    }

    //显示问题类型列表
    public function listQuestion(){
        $keyword = I('keyword', '', 'trim');

        $model = D('Admin/QuestionType');
        $where = array();
        //关键字查询
        if($keyword){
            $where['type_name'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getQuestionTypeList($where);


        $this->keyword = $keyword;
        $this->assign('list', $list['info']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    // 放入回收站
    public function del(){
        $this->_del('QuestionType', 'id');  //调用父类的方法
    }
}