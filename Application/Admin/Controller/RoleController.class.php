<?php
/**
 * 权限管理之角色操作类
 */
namespace Admin\Controller;
use Think\Controller;
class RoleController extends CommonController {
    //角色列表
    public function listRole(){
        $roleModel = D('Admin/Role');
        //查询该角色所拥有的权限，并分页显示
        $data = $roleModel->getRoleInfo();
    	$this->data = $data['role'];
        $this->display();
    }

    //编辑角色
    public function editRole(){
        $id = I('id', 0, 'intval');
        $roleModel = D('Admin/Role');
    	if(IS_POST){
            if ($id > 0){
                if($roleModel->create(I('post.'), 2)){
                    if ($roleModel->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                }
            } else {
                if($roleModel->create(I('post.'), 1)){
                    if ($roleModel->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $roleModel->getError()));
    	}
        //根据id取出角色信息
    	$data = $roleModel->find($id);

    	//取出所有的权限
    	//$priModel = D('Admin/Privilege');
    	//$priData = $priModel->getTree();

    	//取出当前角色所拥有的权限的ID
    	//$rpData = M('RolePrivilege')->field('GROUP_CONCAT(pri_id) pri_id')->where(array('role_id'=>array('eq', $id)))->find();
        $this->assign('data', $data);
        //$this->assign('priData', $priData);
    	//$this->assign('pri_id', $rpData['pri_id']);
		$this->display();
    }
    
    //ajac删除角色
    public function del(){
        $this->_del('role', 'id');
    }
    //获取菜单数据展示
    public function getMenuData(){
    	// 取出所有的权限
    	$priModel = D('Admin/Privilege');
    	$priData = $priModel->field('id,parent_id,pri_name name')->select();
    	$Tree = new \Admin\Controller\BuildTreeArrayController($priData,'id','parent_id','0');
    	$data=$Tree->getTreeArray();
    	$this->ajaxReturn($data);
    }
    //获取有权限的菜单数据展示
    public function getPriMenuData(){
    	$id=I('roleid');
    	// 取出所有的权限
    	$menuModel = D('Admin/Privilege');
    	$menuData = $menuModel->field('id,parent_id,pri_name name')->select();
    	//取出当前角色所拥有的权限的ID
    	$rpData = M('RolePrivilege')->field('pri_id')->where(array('role_id'=>array('eq', $id)))->select();
    	foreach ($menuData as $k=>$v){
    		if (self::isPriChecked($rpData,$v)){
    			$menuData[$k]['checked']='true';
    		}
    	}
    	$Tree = new \Admin\Controller\BuildTreeArrayController($menuData,'id','parent_id','0');
    	$data=$Tree->getTreeArray();
    	$this->ajaxReturn($data);
    }
    //获取该菜单是否有权限
    private function isPriChecked($rpData,$vv){
    	$isChecked = false;
    	foreach ($rpData as $k=>$v){
    		if($v['pri_id']==$vv['id']){
    			$isChecked = true;
				break;
    		}
    	}
    	return $isChecked;
    }
}