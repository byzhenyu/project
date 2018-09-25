<?php
/**
 * 公司性质
 */
namespace Admin\Controller;
use Think\Controller;
class CompanyNatureController extends CommonController {

    //公司性质添加/编辑
    public function editCompanyNature(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/CompanyNature');
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
        $nature = $model->getCompanyNatureInfo(array('id' => $id));
        $this->info = $nature;
        $this->display();
    }

    //公司性质列表
    public function listCompanyNature(){
        $keyword = I('keyword', '', 'trim');
        $model = D('Admin/CompanyNature');
        $where = array();
        if($keyword){
            $where['nature_name'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getCompanyNatureList($where);
        $this->keyword = $keyword;
        $this->list = $list;
        $this->display();
    }

    public function del(){
        $this->_del('CompanyNature', 'id');
    }
}