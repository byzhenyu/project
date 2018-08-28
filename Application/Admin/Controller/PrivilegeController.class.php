<?php
/**
 * 权限管理之权限操作类
 */
namespace Admin\Controller;
use Think\Controller;
class PrivilegeController extends CommonController {

    //编辑权限操作
    public function editPrivilege(){
        $id = I('id', 0, 'intval');
        $privilegeModel = D('Privilege');
    	if(IS_POST){
            if ($id > 0){
                if($privilegeModel->create(I('post.'), 2)){
                    if ($privilegeModel->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
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
        $this->assign('privilege', $privilege);
        $this->assign('privilegelist', $privilegeList);
        $this->display();
    }
    
    //删除权限操作
    public function del(){
    	$this->_del('privilege', 'id');
    }
    
    //权限列表显示 
    public function listPrivilege(){
    	$privilegeModel = D('Admin/Privilege');
        $privilegeData = $privilegeModel->getTree();
    	$this->assign('privilegeData',$privilegeData);
    	$this->display();
    }
}