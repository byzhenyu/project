<?php
/**
 * 敏感词操作类
 */
namespace Admin\Controller;
use Think\Controller;
class ContrabandController extends CommonController {

    //敏感词添加/编辑
    public function editContraband(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/Contraband');
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
        $contraband = M('Contraband')->find($id);
        $this->assign('info', $contraband);
        $this->display();
    }

    //显示过滤关键词列表
    public function listContraband(){
        $keyword = I('keyword', '', 'trim');

        $model = D('Admin/Contraband');
        $where = array();
        //关键字查询
        if($keyword){
            $where['contraband'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getContrabandList($where);


        $this->keyword = $keyword;
        $this->assign('list', $list['info']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    // 放入回收站
    public function del(){
        $this->_del('contraband');  //调用父类的方法
    }
}