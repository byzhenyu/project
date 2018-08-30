<?php
/**
 * 行业信息
 */
namespace Admin\Controller;
use Think\Controller;
class IndustryController extends CommonController {

    //编辑行业信息
    public function editIndustry(){
        $id = I('id', 0, 'intval');
        $privilegeModel = D('Industry');
    	if(IS_POST){
            if ($id > 0){
                if($privilegeModel->create(I('post.'), 2)){
                    if ($privilegeModel->where(array('id' => $id))->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                    else{
                        $this->ajaxReturn(V(0, $privilegeModel->getError()));
                    }
                }
                else{
                    $this->ajaxReturn($privilegeModel->getError());
                }
            } else {
                if($privilegeModel->create(I('post.'), 1)){
                    if ($privilegeModel->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $privilegeModel->getError()));;
    	}

    	$privilege = $privilegeModel->find($id);
        $privilegeList = $privilegeModel->getTree();
        $this->assign('info', $privilege);
        $this->assign('industryList', $privilegeList);
        $this->display();
    }
    
    //删除权限操作
    public function del(){
    	$this->_del('industry', 'id');
    }
    
    //权限列表显示 
    public function listIndustry(){
    	$privilegeModel = D('Admin/Industry');
        $privilegeData = $privilegeModel->getTree();
    	$this->assign('listIndustry',$privilegeData);
    	$this->display();
    }
}